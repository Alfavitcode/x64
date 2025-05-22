$(document).on('click', '.btn-add-to-cart', function(e) {
    e.preventDefault();
    var $btn = $(this);
    var productId = $btn.data('product-id');
    var quantity = 1;
    $btn.prop('disabled', true);
    $.post('/ajax/add_to_cart.php', { product_id: productId, quantity: quantity }, function(response) {
        if (response.success) {
            showNotification('Товар добавлен в корзину!');
        } else {
            showNotification(response.message || 'Ошибка добавления в корзину', 'error');
        }
        $btn.prop('disabled', false);
    }, 'json').fail(function() {
        showNotification('Ошибка добавления в корзину', 'error');
        $btn.prop('disabled', false);
    });
});

function showNotification(message, type) {
    type = type || 'success';
    if ($('.cart-toast').length === 0) {
        $('body').append('<div class="cart-toast"></div>');
    }
    var $toast = $('<div class="cart-toast cart-toast-' + type + '">' + message + '</div>');
    $('.cart-toast').remove();
    $('body').append($toast);
    setTimeout(function() { $toast.fadeOut(400, function() { $toast.remove(); }); }, 2000);
} 