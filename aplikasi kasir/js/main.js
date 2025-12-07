/*
 * Cash Register System - Main JavaScript
 *
 * Fungsi-fungsi utama untuk sistem kasir:
 * - Navigasi antar bagian (Dashboard, Products, Sales, Reports)
 * - Manajemen produk
 * - Fungsi penjualan dan keranjang belanja
 * - Proses pembayaran dan cetak struk
 */

// ========================================
// NAVIGATION FUNCTIONS
// ========================================

/**
 * Menampilkan section tertentu dan menyembunyikan section lainnya
 * @param {string} sectionId - ID section yang akan ditampilkan
 */
function showSection(sectionId) {
    // Sembunyikan semua section
    const sections = document.querySelectorAll('.section');
    sections.forEach(section => {
        section.classList.remove('active');
    });

    // Hapus class active dari semua tombol navigasi
    const navButtons = document.querySelectorAll('.nav-btn');
    navButtons.forEach(button => {
        button.classList.remove('active');
    });

    // Tampilkan section terpilih
    document.getElementById(sectionId).classList.add('active');

    // Tambahkan class active ke tombol yang diklik
    event.target.classList.add('active');

    // Muat konten berdasarkan section
    switch(sectionId) {
        case 'dashboard':
            updateDashboard();
            break;
        case 'products':
            loadProducts();
            break;
        case 'sales':
            loadAvailableProducts();
            break;
        case 'reports':
            loadSalesReport();
            break;
    }
}


// ========================================
// PRODUCT MANAGEMENT FUNCTIONS
// ========================================

/**
 * Memuat daftar produk dari API
 */
function loadProducts() {
    fetch('api/product_api.php?action=getProducts')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                displayProducts(data.data);
            } else {
                console.error('Gagal memuat produk:', data.message);
            }
        })
        .catch(error => {
            console.error('Error saat memuat produk:', error);
        });
}

/**
 * Menampilkan produk dalam tabel
 * @param {Array} products - Array produk untuk ditampilkan
 */
function displayProducts(products) {
    const tbody = document.getElementById('productTableBody');
    tbody.innerHTML = '';

    products.forEach(product => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${product.id}</td>
            <td>${product.name}</td>
            <td>Rp ${parseFloat(product.price).toLocaleString()}</td>
            <td>${product.stock}</td>
        `;
        tbody.appendChild(row);
    });
}

// ========================================
// SALES FUNCTIONS
// ========================================

// Keranjang belanja global
let cart = [];

/**
 * Memuat produk yang tersedia untuk penjualan
 */
function loadAvailableProducts() {
    fetch('api/product_api.php?action=getProducts')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                displayAvailableProducts(data.data);
            } else {
                console.error('Gagal memuat produk:', data.message);
            }
        })
        .catch(error => {
            console.error('Error saat memuat produk:', error);
        });
}

/**
 * Menampilkan produk yang tersedia untuk ditambahkan ke keranjang
 * @param {Array} products - Array produk yang tersedia
 */
function displayAvailableProducts(products) {
    const productsList = document.getElementById('productsList');
    productsList.innerHTML = '';

    products.forEach(product => {
        const productItem = document.createElement('div');
        productItem.className = 'product-item';
        productItem.innerHTML = `
            <h4>${product.name}</h4>
            <p>Rp ${parseFloat(product.price).toLocaleString()}</p>
            <p>Stok: ${product.stock}</p>
        `;
        productItem.onclick = () => addToCart(product);
        productsList.appendChild(productItem);
    });
}

/**
 * Menambahkan produk ke keranjang belanja
 * @param {Object} product - Produk yang akan ditambahkan
 */
function addToCart(product) {
    // Cek apakah produk sudah ada di keranjang
    const existingItem = cart.find(item => item.id === product.id);

    // Cek apakah stok tersedia
    if (existingItem) {
        if (existingItem.quantity + 1 > product.stock) {
            alert('Stok tidak mencukupi!');
            return;
        }
        // Jika produk sudah ada di keranjang, tambahkan jumlahnya
        existingItem.quantity += 1;
    } else {
        if (1 > product.stock) {
            alert('Stok tidak mencukupi!');
            return;
        }
        // Jika produk belum ada di keranjang, tambahkan
        cart.push({
            id: product.id,
            name: product.name,
            price: parseFloat(product.price),
            quantity: 1
        });
    }

    updateCartDisplay();
}

/**
 * Memperbarui tampilan keranjang belanja
 */
function updateCartDisplay() {
    const cartItems = document.getElementById('cartItems');
    cartItems.innerHTML = '';

    let subtotal = 0;

    cart.forEach(item => {
        const itemTotal = item.price * item.quantity;
        subtotal += itemTotal;

        const cartItem = document.createElement('div');
        cartItem.className = 'cart-item';
        cartItem.innerHTML = `
            <div>
                <span>${item.name} x${item.quantity}</span>
            </div>
            <div>
                <span>Rp ${itemTotal.toLocaleString()}</span>
                <button class="btn btn-danger" onclick="removeFromCart(${item.id})" style="padding: 2px 6px; font-size: 12px;">X</button>
            </div>
        `;
        cartItems.appendChild(cartItem);
    });

    // Perbarui ringkasan
    document.getElementById('subtotal').textContent = 'Rp ' + subtotal.toLocaleString();
    document.getElementById('total').textContent = 'Rp ' + subtotal.toLocaleString();

    // Hitung kembalian saat jumlah pembayaran diubah
    calculateChange(subtotal);
}

/**
 * Menghapus produk dari keranjang
 * @param {number} productId - ID produk yang akan dihapus
 */
function removeFromCart(productId) {
    cart = cart.filter(item => item.id !== productId);
    updateCartDisplay();
}

/**
 * Menghitung dan menampilkan jumlah kembalian
 * @param {number} subtotal - Jumlah total pembelian
 */
function calculateChange(subtotal) {
    const paymentInput = document.getElementById('payment');
    const changeDisplay = document.getElementById('change');

    paymentInput.oninput = function() {
        const payment = parseFloat(this.value) || 0;
        const change = payment - subtotal;
        changeDisplay.textContent = 'Rp ' + Math.max(0, change).toLocaleString();
    };
}

/**
 * Memproses pembayaran
 */
function processPayment() {
    if (cart.length === 0) {
        alert('Keranjang kosong. Silakan tambahkan item ke keranjang terlebih dahulu.');
        return;
    }

    const payment = parseFloat(document.getElementById('payment').value) || 0;
    const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);

    if (payment < total) {
        alert('Jumlah pembayaran tidak mencukupi. Silakan masukkan uang yang lebih banyak.');
        return;
    }

    // Proses pembayaran melalui API
    fetch('api/sales_api.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            action: 'processPayment',
            items: cart,
            payment_amount: payment,
            payment_method: 'cash' // Metode pembayaran default
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert(`Pembayaran berhasil!\nTotal: Rp ${data.total.toLocaleString()}\nPembayaran: Rp ${payment.toLocaleString()}\nKembalian: Rp ${data.change.toLocaleString()}`);

            // Generate struk
            generateReceipt(data.transaction_id);

            // Reset keranjang
            cart = [];
            document.getElementById('payment').value = '';
            updateCartDisplay();
        } else {
            alert('Pembayaran gagal: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error saat memproses pembayaran:', error);
        alert('Error saat memproses pembayaran');
    });
}

/**
 * Membuka halaman struk
 * @param {number} transactionId - ID transaksi
 */
function generateReceipt(transactionId) {
    // Buka halaman struk di jendela baru
    window.open(`api/receipt_api.php?transaction_id=${transactionId}&format=html`, '_blank');
}

// ========================================
// REPORTS FUNCTIONS
// ========================================

/**
 * Memuat laporan penjualan (fungsi placeholder)
 */
function loadSalesReport() {
    // Set default dates to last 7 days
    const endDate = new Date();
    const startDate = new Date();
    startDate.setDate(endDate.getDate() - 7);

    document.getElementById('startDate').valueAsDate = startDate;
    document.getElementById('endDate').valueAsDate = endDate;

    // Generate report with default dates
    generateSalesReport();
}

/**
 * Generate sales report based on selected date range
 */
function generateSalesReport() {
    // Check if Chart.js is loaded
    if (typeof Chart === 'undefined') {
        alert('Chart.js library is not loaded. Please check your internet connection.');
        return;
    }

    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;

    // Validate dates
    if (!startDate || !endDate) {
        alert('Please select both start and end dates');
        return;
    }

    // Show loading message
    const canvas = document.getElementById('salesChart');
    const ctx = canvas.getContext('2d');
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    ctx.font = '16px Arial';
    ctx.textAlign = 'center';
    ctx.fillText('Loading report...', canvas.width / 2, canvas.height / 2);

    // Fetch sales report data from the API
    fetch(`api/sales_api.php?action=getSalesReport&start_date=${startDate}&end_date=${endDate}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('API Response:', data); // For debugging
            if (data.status === 'success') {
                displaySalesReport(data.data);
            } else {
                console.error('Gagal memuat laporan penjualan:', data.message);
                alert('Failed to load sales report: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error saat memuat laporan penjualan:', error);
            // Check if it's a network error or response parsing error
            if (error.name === 'TypeError' && error.message.includes('JSON')) {
                alert('Error parsing response from server. Please check console for details.');
            } else {
                alert('Error loading sales report: ' + error.message);
            }
        });
}

/**
 * Display sales report in a chart
 * @param {Array} reportData - Sales report data
 */
function displaySalesReport(reportData) {
    const canvas = document.getElementById('salesChart');

    // Ensure canvas has proper dimensions
    if (canvas.width === 0 || canvas.height === 0) {
        canvas.width = 800;
        canvas.height = 400;
    }

    const ctx = canvas.getContext('2d');

    // Destroy existing chart if it exists
    if (window.salesChart) {
        if (typeof window.salesChart.destroy === 'function') {
            window.salesChart.destroy();
        }
    }

    // Prepare data for the chart
    const labels = reportData.map(item => item.date);
    const transactionCounts = reportData.map(item => item.transaction_count);
    const dailyTotals = reportData.map(item => parseFloat(item.daily_total));

    // Create the chart
    window.salesChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Number of Transactions',
                data: transactionCounts,
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                yAxisID: 'y'
            }, {
                label: 'Daily Sales (Rp)',
                data: dailyTotals,
                borderColor: 'rgb(255, 99, 132)',
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Number of Transactions'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Sales Amount (Rp)'
                    },
                    grid: {
                        drawOnChartArea: false,
                    },
                }
            }
        }
    });
}

// ========================================
// DASHBOARD FUNCTIONS
// ========================================

/**
 * Memperbarui data dashboard dari API
 */
function updateDashboard() {
    fetch('api/sales_api.php?action=getDashboardData')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const dashboardData = data.data;
                // Update Today's Sales
                document.querySelectorAll('.dashboard-cards .value')[0].textContent = 'Rp ' + dashboardData.today_sales.toLocaleString();
                // Update Number of Transactions
                document.querySelectorAll('.dashboard-cards .value')[1].textContent = dashboardData.transaction_count;
                // Update Top Selling Items
                document.querySelectorAll('.dashboard-cards .value')[2].textContent = dashboardData.top_products || '-';
            } else {
                console.error('Gagal memuat data dashboard:', data.message);
                // Fallback ke nilai default
                const valueElements = document.querySelectorAll('.dashboard-cards .value');
                valueElements[0].textContent = 'Rp 0';
                valueElements[1].textContent = '0';
                valueElements[2].textContent = '-';
            }
        })
        .catch(error => {
            console.error('Error saat memuat dashboard:', error);
            // Fallback ke nilai default
            document.querySelector('.dashboard-cards .value:nth-child(1)').textContent = 'Rp 0';
            document.querySelector('.dashboard-cards .value:nth-child(2)').textContent = '0';
            document.querySelector('.dashboard-cards .value:nth-child(3)').textContent = '-';
        });
}

// ========================================
// INITIALIZATION
// ========================================

/**
 * Inisialisasi aplikasi saat DOM siap
 */
document.addEventListener('DOMContentLoaded', function() {
    // Muat dashboard secara default
    showSection('dashboard');
});