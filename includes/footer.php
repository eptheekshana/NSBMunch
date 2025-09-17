<!-- Footer -->
    <footer class="footer mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5><i class="fas fa-utensils me-2"></i>NSBMunch</h5>
                    <p>Your campus food ordering solution. Making campus dining convenient and delicious!</p>
                </div>
                <div class="col-md-4">
                    <h5><i class="fas fa-envelope me-2"></i>Contact Us</h5>
                    <p>Email: <?php echo SITE_EMAIL; ?></p>
                    <p>Campus: NSBM Green University</p>
                </div>
                <div class="col-md-4">
                    <h5><i class="fas fa-code me-2"></i>Developers</h5>
                    <p>Software Engineering Students</p>
                    <p>NSBM Green University</p>
                </div>
            </div>
            <hr class="my-4">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0">&copy; <?php echo date('Y'); ?> NSBMunch. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <small>Developed for NSBM Green University Campus</small>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        // Auto-hide alerts after 5 seconds
        $(document).ready(function() {
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);
            
            // Confirm delete actions
            $('.delete-btn').click(function(e) {
                if (!confirm('Are you sure you want to delete this item?')) {
                    e.preventDefault();
                }
            });
            
            // Form validation
            $('form').submit(function(e) {
                var isValid = true;
                $(this).find('input[required], select[required], textarea[required]').each(function() {
                    if ($(this).val() === '') {
                        isValid = false;
                        $(this).addClass('is-invalid');
                    } else {
                        $(this).removeClass('is-invalid');
                    }
                });
                
                if (!isValid) {
                    e.preventDefault();
                    alert('Please fill in all required fields.');
                }
            });
            
            // Number input validation
            $('input[type="number"]').on('input', function() {
                if ($(this).val() < 0) {
                    $(this).val(0);
                }
            });
            
            // Price calculation for cart
            $('.quantity-input').change(function() {
                var row = $(this).closest('tr');
                var price = parseFloat(row.find('.price').data('price'));
                var quantity = parseInt($(this).val()) || 1;
                var total = price * quantity;
                row.find('.total-price').text('Rs. ' + total.toFixed(2));
                
                // Update grand total
                updateGrandTotal();
            });
            
            function updateGrandTotal() {
                var grandTotal = 0;
                $('.total-price').each(function() {
                    var price = parseFloat($(this).text().replace('Rs. ', '')) || 0;
                    grandTotal += price;
                });
                $('#grand-total').text('Rs. ' + grandTotal.toFixed(2));
            }
        });
        
        // Food category filter
        function filterByCategory(category) {
            if (category === '') {
                $('.food-card').show();
            } else {
                $('.food-card').hide();
                $('.food-card[data-category="' + category + '"]').show();
            }
        }
        
        // Shop filter
        function filterByShop(shopId) {
            if (shopId === '') {
                $('.food-card').show();
            } else {
                $('.food-card').hide();
                $('.food-card[data-shop="' + shopId + '"]').show();
            }
        }
        
        // Add to cart function
        function addToCart(foodId) {
            $.ajax({
                url: '<?php echo SITE_URL; ?>/user/functions/add_to_cart.php',
                method: 'POST',
                data: {
                    food_id: foodId,
                    quantity: 1
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert('Item added to cart successfully!');
                        // Update cart count if exists
                        if ($('#cart-count').length) {
                            $('#cart-count').text(parseInt($('#cart-count').text()) + 1);
                        }
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                }
            });
        }
        
        // Update order status
        function updateOrderStatus(orderId, status) {
            if (confirm('Are you sure you want to update this order status?')) {
                $.ajax({
                    url: '<?php echo SITE_URL; ?>/shop_owner/functions/update_order_status.php',
                    method: 'POST',
                    data: {
                        order_id: orderId,
                        status: status
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            alert('Order status updated successfully!');
                            location.reload();
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function() {
                        alert('An error occurred. Please try again.');
                    }
                });
            }
        }
        
        // Image preview for file uploads
        function previewImage(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#image-preview').attr('src', e.target.result).show();
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
        
        // Auto-refresh for order management (every 30 seconds)
        if (window.location.pathname.includes('manage_orders') || window.location.pathname.includes('orders')) {
            setInterval(function() {
                location.reload();
            }, 30000);
        }
    </script>
    
    <?php if (isset($custom_js)): ?>
        <script><?php echo $custom_js; ?></script>
    <?php endif; ?>
</body>
</html>