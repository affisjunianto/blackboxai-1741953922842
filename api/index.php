            url: '/api/?endpoint=check_balance',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                api_key: '<?php echo $credentials['api_key']; ?>',
                api_secret: '<?php echo $credentials['api_secret']; ?>',
                user_id: userId
            }),
