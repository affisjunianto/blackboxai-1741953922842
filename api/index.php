                    <h6 class="text-primary">Check Balance</h6>
                    <p>Retrieve the current balance for a user. Agents can only check their own balance, while admins can check any user's balance.</p>

                    <div class="mb-3">
                        <strong>Endpoint:</strong>
                        <div class="bg-light p-2 rounded">
                            <code>POST /api/?endpoint=check_balance</code>
                        </div>
                    </div>

                    <div class="mb-3">
                        <strong>Request Body (JSON):</strong>
                        <pre class="bg-light p-3 rounded"><code>{
    "api_key": "your_api_key",
    "api_secret": "your_api_secret",
    "user_id": 123
}</code></pre>
                    </div>
