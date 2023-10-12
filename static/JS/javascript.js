$(document).ready(function () {
    // var cart = '<li><a id="openOverlay" href="#">View Cart</a></li>';
    // $('#nav-list').prepend(cart);

    $('#openOverlay').click(function (e) {
        e.preventDefault(); // Prevent the default link behavior
        overlay();
    });

    $('#closeOverlay').click(function (e) {
        e.preventDefault(); // Prevent the default button behavior
        closeOverlay();
    });

    function overlay() {
        const overlay = $('#overlay');
        overlay.show();
        updateTotalPrice();
    }

    function closeOverlay() {
        const overlay = $('#overlay');
        overlay.hide();
    }

    $(".quantity-input").on("change", function() {
        updateTotalPrice();
      });
});

function updateTotalPrice() {
    var total = 0;
    var itemPrices = document.querySelectorAll('.item-price');
    var quantities = document.querySelectorAll('input[name^="quantity["]');

    for (var i = 0; i < itemPrices.length; i++) {
        var price = parseFloat(itemPrices[i].textContent.replace('R', '').replace(',', ''));
        var quantity = parseInt(quantities[i].value);
        total += price * quantity;
    }

    // Display the total price
    document.getElementById('total-price').textContent = 'Total Price: R' + total.toFixed(2);
}

// Attach an event listener to quantity inputs to update total price on change
var quantityInputs = document.querySelectorAll('input[name^="quantity["]');
quantityInputs.forEach(function(input) {
    input.addEventListener('input', updateTotalPrice);
});


updateTotalPrice();

function validateLoginForm() {
    var username = document.getElementById("username").value;
    var password = document.getElementById("password").value;

    if (username === "" || password === "") {
        alert("Please enter both username and password.");
        return false;
    }
    return true;
}

function checkDiscount() {
    var discount = document.getElementById("discount-percent").value;
    if (isNaN(discount) || discount < 1 || discount > 100) {
        alert("Discount must be a number between 1 and 100");
        return false;
    }
    return true;
}

function checkStock() {
    var stock = document.getElementById("stock").value;
    console.log(stock);
    if (isNaN(stock) || stock < 0) {
        alert("Stock must be a number equal to 0 or greater");
        return false;
    }
    return true;
}
