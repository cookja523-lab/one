<?php
// index.php — sandbox-first demo UI for the banking platform
// Safety: this file intentionally disables live operations unless PRODUCTION_APPROVED=yes
// Configure via environment variables:
//   API_ROOT (default sandbox): https://your-backend-sandbox.example.com/api
//   LIVE_MODE (true/false): whether to point to production endpoints
//   PRODUCTION_APPROVED (yes/no): must be "yes" to enable live mode actions
//
// NEVER set PRODUCTION_APPROVED=yes until legal/compliance checks, bank partnerships,
// PCI scope, and provider contracts are in place.
//
// Example (UNIX):
//   export API_ROOT="https://your-backend-sandbox.example.com/api"
//   export LIVE_MODE="false"
//   export PRODUCTION_APPROVED="no"

$apiRootEnv = getenv('API_ROOT');
$defaultApiRoot = 'https://your-backend-sandbox.example.com/api';
$API_ROOT = $apiRootEnv ?: $defaultApiRoot;

$LIVE_MODE = (strtolower(getenv('LIVE_MODE') ?: 'false') === 'true');
$PRODUCTION_APPROVED = (strtolower(getenv('PRODUCTION_APPROVED') ?: 'no') === 'yes');

// If LIVE_MODE is requested but not approved, disable actions and show a clear warning.
$DISABLED = $LIVE_MODE && !$PRODUCTION_APPROVED;
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Banking Platform Demo (PHP) — Sandbox first</title>
  <style>
    body { font-family: system-ui, -apple-system, "Segoe UI", Roboto, Arial; margin: 2rem; color:#222 }
    header { margin-bottom: 1.5rem }
    .card { border: 1px solid #e1e4e8; padding:1rem; border-radius:8px; margin-bottom:1rem; }
    label { display:block; margin-top:.5rem; font-size:.9rem; color:#444 }
    input, select { width:100%; padding:.5rem; margin-top:.25rem; border:1px solid #d0d7de; border-radius:6px }
    button { margin-top:.75rem; padding:.5rem 1rem; border-radius:6px; border:0; background:#0366d6; color:#fff }
    pre { background:#f6f8fa; padding:1rem; border-radius:6px; overflow:auto }
    .warning { background:#ffe3e3; border:1px solid #ffbdbd; padding:.75rem; border-radius:6px; color:#6b0b0b; margin-bottom:1rem }
    .muted { color:#666; font-size:.9rem }
  </style>
</head>
<body>
  <header>
    <h1>Banking Platform — PHP Demo (Sandbox-first)</h1>
    <p class="muted">This page is a safe demo UI. It points to: <strong id="apiRoot"><?php echo htmlspecialchars($API_ROOT); ?></strong></p>

    <?php if ($DISABLED): ?>
      <div class="warning">
        LIVE MODE requested but NOT ENABLED — actions are disabled.
        To enable live operations you must set the environment variable <code>PRODUCTION_APPROVED=yes</code>
        only after you have finalized legal, compliance, bank/processor contracts and completed PCI/KYC/AML checks.
      </div>
    <?php elseif ($LIVE_MODE): ?>
      <div class="warning" style="background:#fff6d6;border-color:#ffe7a3;color:#6b4b00">
        LIVE MODE is ENABLED. Only enable this after legal/compliance approvals and provider contracts are in place.
      </div>
    <?php else: ?>
      <div class="muted">Running in sandbox mode (recommended for development).</div>
    <?php endif; ?>
  </header>

  <section class="card">
    <h2>Create user (sandbox)</h2>
    <label>Email <input id="email" type="email" value="demo@example.com"></label>
    <label>Name <input id="name" type="text" value="Demo User"></label>
    <button id="createUserBtn">Create User</button>
    <pre id="createUserOut">Response will appear here</pre>
  </section>

  <section class="card">
    <h2>Create virtual card (sandbox)</h2>
    <label>Account ID <input id="accountId" type="text" value="acct_sandbox_123"></label>
    <label>Card Type
      <select id="cardType">
        <option value="virtual">Virtual</option>
        <option value="physical">Physical</option>
      </select>
    </label>
    <button id="createCardBtn">Create Virtual Card</button>
    <pre id="createCardOut">Response will appear here</pre>
  </section>

  <section class="card">
    <h2>Simulate internal transfer (sandbox)</h2>
    <label>From Account <input id="fromAcct" value="acct_sandbox_123"></label>
    <label>To Account <input id="toAcct" value="acct_sandbox_456"></label>
    <label>Amount <input id="amount" value="10.00"></label>
    <button id="transferBtn">Transfer</button>
    <pre id="transferOut">Response will appear here</pre>
  </section>

  <footer style="margin-top:2rem; color:#666; font-size:.9rem">
    <div>Server-side config:</div>
    <ul>
      <li>API_ROOT = <?php echo htmlspecialchars($API_ROOT); ?></li>
      <li>LIVE_MODE = <?php echo $LIVE_MODE ? 'true' : 'false'; ?></li>
      <li>PRODUCTION_APPROVED = <?php echo $PRODUCTION_APPROVED ? 'yes' : 'no'; ?></li>
    </ul>
    <div class="muted">Next: connect this UI to your backend sandbox endpoints and to provider sandboxes (Plaid, Marqeta/Stripe Issuing, etc.).</div>
  </footer>

  <script>
    // API root and safety flags populated from server-side PHP
    const API_ROOT = '<?php echo addslashes($API_ROOT); ?>';
    const DISABLED = <?php echo $DISABLED ? 'true' : 'false'; ?>;
    const LIVE_MODE = <?php echo $LIVE_MODE ? 'true' : 'false'; ?>;

    function setDisabledState() {
      if (DISABLED) {
        document.querySelectorAll('button').forEach(b => { b.disabled = true; b.style.opacity = '0.6'; });
        const msg = '\n\nLive mode requested but not approved. Contact the administrator to enable production after compliance is complete.';
        document.querySelectorAll('pre').forEach(p => p.textContent = p.textContent + msg);
      }
    }

    async function post(path, body) {
      try {
        const res = await fetch(API_ROOT + path, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(body),
          credentials: 'omit'
        });
        const t = await res.text();
        // Attempt to parse JSON, else return text
        try { return { ok: res.ok, status: res.status, data: JSON.parse(t) }; }
        catch(e) { return { ok: res.ok, status: res.status, data: t }; }
      } catch (err) {
        return { ok:false, error: String(err) };
      }
    }

    document.getElementById('createUserBtn').onclick = async () => {
      if (DISABLED) return setDisabledState();
      const email = document.getElementById('email').value;
      const name = document.getElementById('name').value;
      const out = await post('/sandbox/users', { email, name });
      document.getElementById('createUserOut').textContent = JSON.stringify(out, null, 2);
    };

    document.getElementById('createCardBtn').onclick = async () => {
      if (DISABLED) return setDisabledState();
      const accountId = document.getElementById('accountId').value;
      const cardType = document.getElementById('cardType').value;
      const out = await post('/sandbox/cards', { accountId, cardType });
      document.getElementById('createCardOut').textContent = JSON.stringify(out, null, 2);
    };

    document.getElementById('transferBtn').onclick = async () => {
      if (DISABLED) return setDisabledState();
      const from = document.getElementById('fromAcct').value;
      const to = document.getElementById('toAcct').value;
      const amount = document.getElementById('amount').value;
      const out = await post('/sandbox/transfers', { from, to, amount });
      document.getElementById('transferOut').textContent = JSON.stringify(out, null, 2);
    };

    // On page load enforce safety state
    setDisabledState();
  </script>
</body>
</html>





<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Pen Pal Online Bank</title>
</head>
<body>
    <h2>Welcome, {{ current_user.username }}</h2>
    <p>Balance: $1000000000000000000000000000{{ "%.2f"|format(balance) }}</p>
    <a href="{{ url_for('logout') }}">Logout</a>
    <a href="{{ url_for('account') }}">Account Settings</a>

    <h3>Deposit</h3>
    <form method="POST" action="{{ url_for('deposit') }}">
        <label for="amount">Amount:</label>
        <input type="number" step="0.01" name="amount" required>
        <button type="submit">Deposit</button>
    </form>

    <h3>Withdraw</h3>
    <form method="POST" action="{{ url_for('withdraw') }}">
        <label for="amount">Amount:</label>
        <input type="number" step="0.01" name="amount" required>
        <button type="submit">Withdraw</button>
    </form>

    <h3>Transfer</h3>
    <form method="POST" action="{{ url_for('transfer') }}">
        <label for="recipient">Recipient Username:</label>
        <input type="text" name="recipient" required>
        <label for="amount">Amount:</label>
        <input type="number" step="0.01" name="amount" required>
        <button type="submit">Transfer</button>
    </form>

    <h3>Transaction History</h3>
    <ul>
        {% for transaction in transactions %}
            <li>{{ transaction.type }}: ${{ "%.2f"|format(transaction.amount) }} {% if transaction.recipient %}to {{ transaction.recipient }}{% endif %}</li>
        {% endfor %}
    </ul>

    {% with messages = get_flashed_messages() %}
        {% if messages %}
            <ul>
                {% for message in messages %}
                    <li>{{ message }}</li>
                {% endfor %}
            </ul>
        {% endif %}
    {% endwith %}
</body>
</html>
