<?php include_once 'header.php' ?>

<!-- Main Content -->
<div class="content-wrapper" id="content">
    <div class="container-fluid">
        <h3 class="mb-4">Cashier</h3>

        <div class="row">
            <!-- Left: New Sale -->
            <div class="col-lg-8 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="fas fa-shopping-cart mr-2"></i> New Sale</h6>
                    </div>
                    <div class="card-body">
                        <!-- Search Medicine -->
                        <div class="form-group">
                            <label for="search_medicine" class="font-weight-bold">Search Medicine</label>
                            <input type="text" id="search_medicine" class="form-control form-control-lg" placeholder="🔎 Enter medicine name or code">
                        </div>

                        <!-- Cart Table -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="cart_table">
                                <thead class="thead-light">
                                    <tr>
                                        <th style="width: 35%;">Medicine</th>
                                        <th style="width: 10%;">Qty</th>
                                        <th style="width: 15%;">Price</th>
                                        <th style="width: 15%;">Total</th>
                                        <th class="text-center" style="width: 10%;">Remove</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Items will be added dynamically via JS -->
                                </tbody>
                                <tfoot>
                                    <tr class="bg-light">
                                        <th colspan="3" class="text-right h5">Grand Total:</th>
                                        <th id="grand_total" class="h5 text-success">₱0.00</th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- Checkout Actions -->
                        <div class="d-flex justify-content-between mt-4">
                            <button class="btn btn-secondary btn-lg">
                                <i class="fas fa-times mr-1"></i> Cancel
                            </button>
                            <button class="btn btn-success btn-lg" id="checkout_btn">
                                <i class="fas fa-cash-register mr-1"></i> Checkout
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Transaction Summary -->
            <div class="col-lg-4 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-dark text-white">
                        <h6 class="mb-0"><i class="fas fa-receipt mr-2"></i> Transaction Summary</h6>
                    </div>
                    <div class="card-body">
                        <p><strong>Date:</strong> <?= date("F j, Y g:i A") ?></p>
                        <p><strong>Cashier:</strong> <?= e($user['full_name']); ?></p>
                        <hr>
                        <div class="form-group">
                            <label for="customer_name">Customer Name</label>
                            <input type="text" id="customer_name" class="form-control" placeholder="Walk-in Customer">
                        </div>
                        <div class="form-group">
                            <label for="payment_method">Payment Method</label>
                            <select id="payment_method" class="custom-select">
                                <option value="cash" selected>Cash</option>
                                <option value="gcash">GCash</option>
                                <option value="card">Credit/Debit Card</option>
                            </select>
                        </div>
                        <div class="alert alert-info text-center">
                            <h5>Total to Pay:</h5>
                            <h3 id="summary_total">₱0.00</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-history mr-2"></i> Recent Transactions</h6>
            </div>
            <div class="card-body">
                <table class="table table-striped table-bordered datatable">
                    <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Payment</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Example Data -->
                        <tr>
                            <td>1</td>
                            <td>Walk-in</td>
                            <td>₱550.00</td>
                            <td>Cash</td>
                            <td>Sept 18, 2025 5:45 PM</td>
                        </tr>
                        <!-- Replace with DB results -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Footer -->
<?php include_once 'footer.php'; ?>