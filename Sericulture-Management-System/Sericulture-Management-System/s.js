// Open order form
function openOrder(productName, price) {
  document.getElementById("orderModal").style.display = "block";
  document.getElementById("product").value = productName;
  document.getElementById("price").value = price;
}

// Close order form
function closeOrder() {
  document.getElementById("orderModal").style.display = "none";
}

// Close payment
function closePayment() {
  document.getElementById("paymentModal").style.display = "none";
}

// Handle order submit
document.getElementById("orderForm").addEventListener("submit", function(e) {
  e.preventDefault();

  const product = document.getElementById("product").value;
  const price = parseFloat(document.getElementById("price").value);
  const customerName = document.getElementById("customerName").value;
  const mobile = document.getElementById("mobile").value;
  const area = document.getElementById("area").value;
  const quantity = parseInt(document.getElementById("quantity").value);

  const total = (price * quantity).toFixed(2);

  // Generate Bill
  document.getElementById("billSection").innerHTML = `
    <div id="invoice">
      <h3>Customer Bill</h3>
      <p><strong>Name:</strong> ${customerName}</p>
      <p><strong>Mobile:</strong> ${mobile}</p>
      <p><strong>Area:</strong> ${area}</p>
      <p><strong>Product:</strong> ${product}</p>
      <p><strong>Price:</strong> ₹${price}</p>
      <p><strong>Quantity:</strong> ${quantity}</p>
      <p><strong>Total:</strong> ₹${total}</p>
    </div>
  `;

  closeOrder();
  document.getElementById("paymentModal").style.display = "block";
});

// Confirm Payment
function confirmPayment() {
  const method = document.getElementById("paymentMethod").value;
  if (!method) {
    alert("⚠️ Please select a payment method!");
    return;
  }
  alert("✅ Payment successful using " + method);

  // Download PDF bill
  const invoice = document.getElementById("invoice");
  html2pdf().from(invoice).save("Customer_Bill.pdf");

  closePayment();
}
