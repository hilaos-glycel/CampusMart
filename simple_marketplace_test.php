<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Marketplace Test</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1200px; margin: 0 auto; padding: 20px; }
        .product-card { border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 8px; }
        .price { color: #28a745; font-weight: bold; font-size: 18px; }
        .condition { background: #e9ecef; padding: 4px 8px; border-radius: 4px; font-size: 12px; }
    </style>
</head>
<body>
    <h1>Simple Marketplace Test</h1>
    
    <div id="status">Loading...</div>
    <div id="products"></div>
    
    <script>
    console.log('Starting simple marketplace test...');
    
    // Test API call
    fetch('api/get_listings.php')
        .then(response => {
            console.log('Response status:', response.status);
            console.log('Response ok:', response.ok);
            return response.text();
        })
        .then(text => {
            console.log('Raw response length:', text.length);
            console.log('Raw response:', text.substring(0, 500) + '...');
            
            document.getElementById('status').innerHTML = `
                <p><strong>API Response Status:</strong> OK</p>
                <p><strong>Response Length:</strong> ${text.length} characters</p>
            `;
            
            try {
                const data = JSON.parse(text);
                console.log('JSON parsed successfully');
                console.log('Data:', data);
                
                if (data.success) {
                    document.getElementById('status').innerHTML += `
                        <p style="color: green;"><strong>✅ API Success!</strong></p>
                        <p><strong>Listings found:</strong> ${data.listings ? data.listings.length : 0}</p>
                    `;
                    
                    if (data.listings && data.listings.length > 0) {
                        let html = '<h2>Products:</h2>';
                        
                        data.listings.forEach(product => {
                            html += `
                                <div class="product-card">
                                    <h3>${product.title}</h3>
                                    <p>${product.description}</p>
                                    <p class="price">₱${parseFloat(product.price).toLocaleString()}</p>
                                    <p>Condition: <span class="condition">${product.condition_item}</span></p>
                                    <p>Seller: ${product.seller_name}</p>
                                    <p>Category: ${product.category_name}</p>
                                    <p>Status: ${product.status || 'N/A'}</p>
                                </div>
                            `;
                        });
                        
                        document.getElementById('products').innerHTML = html;
                        
                    } else {
                        document.getElementById('products').innerHTML = '<p>No products found.</p>';
                    }
                    
                } else {
                    document.getElementById('status').innerHTML += `
                        <p style="color: red;"><strong>❌ API Error</strong></p>
                        <p>Message: ${data.message || 'Unknown error'}</p>
                    `;
                }
                
            } catch (e) {
                console.error('JSON parse error:', e);
                document.getElementById('status').innerHTML += `
                    <p style="color: red;"><strong>❌ JSON Parse Error:</strong> ${e.message}</p>
                    <details>
                        <summary>Raw Response</summary>
                        <pre style="background: #f8f8f8; padding: 10px; overflow-x: auto;">${text}</pre>
                    </details>
                `;
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            document.getElementById('status').innerHTML = `
                <p style="color: red;"><strong>❌ Fetch Error:</strong> ${error.message}</p>
            `;
        });
    </script>
</body>
</html>
