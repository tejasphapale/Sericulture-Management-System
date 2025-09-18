const mainContent = document.getElementById('mainContent');
const orderModal = document.getElementById('orderModal');
const paymentModal = document.getElementById('paymentModal');
const header = document.querySelector("header");
const footer = document.querySelector("footer");

// ------------------ BLUR ------------------
function addBlur() {
  mainContent.classList.add('blur');
  if (header) header.classList.add('blur');
  if (footer) footer.classList.add('blur');
}
function removeBlur() {
  mainContent.classList.remove('blur');
  if (header) header.classList.remove('blur');
  if (footer) footer.classList.remove('blur');
}

// ------------------ ORDER MODAL ------------------
function openOrder(productName) {
  document.getElementById("product").value = productName;
  orderModal.style.display = "flex";
  addBlur();
}
function closeOrder() {
  orderModal.style.display = "none";
  removeBlur();
}

// ------------------ PAYMENT MODAL ------------------
function openPayment(details) {
  paymentModal.style.display = "flex";
  addBlur();

  document.getElementById("paymentDetails").innerHTML = `
    <span class="close-btn" onclick="closePayment()">&times;</span>
    <h2 style="text-align:center;">🧾 Customer Bill</h2>
    <p><strong>Name:</strong> ${details.name}</p>
    <p><strong>Mobile:</strong> ${details.mobile}</p>
    <p><strong>Address:</strong> ${details.address}</p>
    <p><strong>Product:</strong> ${details.product}</p>
    <p><strong>Quantity:</strong> ${details.quantity}</p>
    <p><strong>Price:</strong> ₹${details.price}</p>
    <p><strong>Total:</strong> ₹${details.total}</p>

    <h3>Payment Method</h3>
    <select id="paymentMethod">
      <option value="">Select</option>
      <option value="UPI">UPI</option>
      <option value="Card">Card</option>
      <option value="Cash">Cash on Delivery</option>
    </select>
    <button onclick='confirmPayment(${JSON.stringify(details)})'>Confirm Payment</button>

    <div id="qrCodeContainer" style="text-align:center; margin-top:10px;"></div>
  `;
}
function closePayment() {
  paymentModal.style.display = "none";
  removeBlur();
}

// ------------------ ORDER SUBMIT ------------------
document.getElementById('orderForm').addEventListener('submit', async function(e) {
  e.preventDefault();

  const product = document.getElementById("product").value;
  const name = document.getElementById("name").value;
  const mobile = document.getElementById("mobile").value;
  const address = document.getElementById("address").value;
  const quantity = parseInt(document.getElementById("quantity").value);

  let price = 0;
  if (product.includes("Cocoons")) price = 850;
  else if (product.includes("Eggs")) price = 320;
  else if (product.includes("Live Silkworms")) price = 500;
  else if (product.includes("Silk Yarn")) price = 1500;
  else if (product.includes("Mulberry Leaves")) price = 200;
  else if (product.includes("Chrysalis Powder")) price = 750;

  const total = (price * quantity).toFixed(2);

  const orderDetails = { product, name, mobile, address, quantity, price, total };

  try {
    const res = await fetch("order.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(orderDetails)
    });

    const data = await res.json();

    if (data.success) {
      orderDetails.order_id = data.order_id; 
      closeOrder();
      openPayment(orderDetails);
    } else {
      alert("❌ Failed to place order. Try again.");
    }
  } catch (err) {
    console.error(err);
    alert("⚠️ Server error while placing order.");
  }

  this.reset();
});

// ------------------ PAYMENT ------------------
async function confirmPayment(details) {
  const method = document.getElementById("paymentMethod").value;
  if (!method) return alert("Please select a payment method!");

  const qrContainer = document.getElementById("qrCodeContainer");
  qrContainer.innerHTML = "";

  // ----------------- UPI Payment -----------------
  if (method === "UPI") {
      new QRCode(qrContainer, {
          text: `upi://pay?pa=yourupiid@upi&pn=SericultureShop&am=${details.total}&cu=INR`,
          width: 200,
          height: 200
      });
      alert("📲 Scan the QR code to pay ₹" + details.total);
      return;
  }

  // ----------------- Prepare payload for server -----------------
  const payload = {
    order_id: details.order_id,
    amount: details.total,                 // PHP expects amount
    status: method === "Cash" ? "pending" : "success",
    username: details.name,                // PHP expects username
    mobile: details.mobile,
    product_name: details.product,         // PHP expects product_name
    city: details.address,                 // PHP expects city
    payment_method: method === "Cash" ? "Cash on Delivery" : method
  };

  try {
    const res = await fetch("payment.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload)
    });

    const data = await res.json();

    if (data.success) {
      alert("✅ Payment recorded!");

      // ----------------- Map to PDF keys -----------------
      const pdfDetails = {
        name: payload.username,
        mobile: payload.mobile,
        address: payload.city,
        product: payload.product_name,
        quantity: details.quantity,
        price: details.price,
        total: details.total,
        payment_method: payload.payment_method
      };

      downloadBill(pdfDetails); // ✅ Generate PDF even for COD
      closePayment();
    } else {
      alert("❌ Payment failed: " + (data.error || "Unknown error"));
    }

  } catch (err) {
    console.error(err);
    alert("⚠️ Server error while saving payment.");
  }
}


// ------------------ BILL PDF ------------------
function downloadBill(details) {
  const element = document.createElement("div");
  element.innerHTML = `
    <div style="font-family: Arial, sans-serif; max-width: 500px; margin: auto; padding: 20px; border: 2px solid #4CAF50; border-radius: 10px;">
      <h1 style="text-align:center; color:#4CAF50;">🧾 Sericulture Co.</h1>
      <h2 style="text-align:center; margin-bottom: 20px;">Customer Invoice</h2>

      <table style="width:100%; margin-bottom:20px; border-collapse: collapse;">
        <tr>
          <td><strong>Name:</strong> ${details.name}</td>
          <td><strong>Mobile:</strong> ${details.mobile}</td>
        </tr>
        <tr>
          <td colspan="2"><strong>Address:</strong> ${details.address}</td>
        </tr>
      </table>

      <table style="width:100%; border-collapse: collapse; margin-bottom: 20px;">
        <thead>
          <tr style="background:#4CAF50; color:white;">
            <th style="padding:8px; border:1px solid #ddd;">Product</th>
            <th style="padding:8px; border:1px solid #ddd;">Quantity</th>
            <th style="padding:8px; border:1px solid #ddd;">Price (₹)</th>
            <th style="padding:8px; border:1px solid #ddd;">Total (₹)</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td style="padding:8px; border:1px solid #ddd;">${details.product}</td>
            <td style="padding:8px; border:1px solid #ddd;">${details.quantity}</td>
            <td style="padding:8px; border:1px solid #ddd;">${details.price}</td>
            <td style="padding:8px; border:1px solid #ddd;">${details.total}</td>
          </tr>
        </tbody>
      </table>

      <p style="text-align:right; font-size:16px;"><strong>Payment Method:</strong> ${details.payment_method}</p>
      <p style="text-align:center; color:#4CAF50; font-weight:bold; font-size:18px;">Thank You for Your Order!</p>
    </div>
  `;

  html2pdf().from(element).set({
    margin: 10,
    filename: 'Customer_Bill.pdf',
    image: { type: 'jpeg', quality: 0.98 },
    html2canvas: { scale: 2 },
    jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
  }).save();
}

