<?php include_once 'header.php'; ?>

<div class="content-wrapper" id="content">
    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="mb-0">POS (Point of Sale)</h3>
        </div>

        <?php
        $medicines = $db->select_all('medicines', 'name', 'ASC');
        $cart = $_SESSION['cart'] ?? [];
        unset($_SESSION['cart_error'], $_SESSION['cart_success']);

        function peso($amount)
        {
            return '₱' . number_format($amount, 2);
        }
        ?>

        <div class="row">
            <!-- Medicines Selection -->
            <div class="col-lg-5 mb-4">
                <div class="card shadow border-primary">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-medkit mr-2"></i>Medicines</h5>
                    </div>
                    <div class="card-body">
                        <form id="addToCartForm" autocomplete="off">
                            <div class="form-group">
                                <label for="medicine_id">Select Medicine</label>
                                <select class="form-control" id="medicine_id" required>
                                    <option value="" selected disabled>-- Choose Medicine --</option>
                                    <?php foreach ($medicines as $med): ?>
                                        <option value="<?= $med['medicine_id'] ?>" data-price="<?= $med['unit_price'] ?>" data-stock="<?= $med['quantity'] ?>">
                                            <?= htmlspecialchars($med['name']) ?> - <?= peso($med['unit_price']) ?> (Stock: <?= $med['quantity'] ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="quantity">Quantity</label>
                                <input type="number" id="quantity" class="form-control" min="1" value="1" required>
                            </div>

                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-cart-plus mr-2"></i>Add to Cart
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Cart -->
            <div class="col-lg-7 mb-4">
                <div class="card shadow border-success">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-shopping-cart mr-2"></i>Cart</h5>
                    </div>
                    <div class="card-body">
                        <table class="table border" id="cartTable">
                            <thead class="thead-light">
                                <tr>
                                    <th>Medicine</th>
                                    <th>Qty</th>
                                    <th>Unit Price</th>
                                    <th>Total</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($cart)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center">Cart is empty</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($cart as $item): ?>
                                        <tr data-id="<?= $item['id'] ?>">
                                            <td><?= htmlspecialchars($item['name']) ?></td>
                                            <td><?= $item['qty'] ?></td>
                                            <td><?= peso($item['price']) ?></td>
                                            <td><?= peso($item['price'] * $item['qty']) ?></td>
                                            <td>
                                                <button class="btn btn-danger btn-sm removeItem" type="button">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>

                        <div class="d-flex justify-content-between mt-3 font-weight-bold">
                            <span>Total:</span>
                            <span id="cartTotal">
                                <?= peso(array_sum(array_map(fn($i) => $i['price'] * $i['qty'], $cart))) ?>
                            </span>
                        </div>

                        <button class="btn btn-success btn-block mt-3" id="checkoutBtn" <?= empty($cart) ? 'disabled' : '' ?> type="button">
                            <i class="fas fa-credit-card mr-2"></i>Checkout
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Sales -->
        <div class="card shadow mt-4 border-info">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0"><i class="fas fa-history mr-2"></i>Recent Sales</h6>
            </div>
            <div class="card-body">
                <table class="table table-bordered datatable mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Receipt</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $recent_receipts = $db->custom_query("
                            SELECT s.receipt_number, s.sale_date, SUM(si.quantity * si.price) AS total
                            FROM sales s
                            JOIN sale_items si ON s.sale_id = si.sale_id
                            GROUP BY s.receipt_number
                            ORDER BY s.sale_date DESC
                            LIMIT 10
                        ");

                        foreach ($recent_receipts as $receipt):
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($receipt['receipt_number']) ?></td>
                                <td>
                                    <?php
                                    $items = $db->custom_query("
                                    SELECT m.name, si.quantity, si.price
                                    FROM sale_items si
                                    JOIN medicines m ON si.medicine_id = m.medicine_id
                                    JOIN sales s ON si.sale_id = s.sale_id
                                    WHERE s.receipt_number = ?
                                ", [$receipt['receipt_number']]);
                                    foreach ($items as $item) {
                                        echo htmlspecialchars($item['name']) . " (" . $item['quantity'] . "), ";
                                    }
                                    ?>
                                </td>
                                <td><?= peso($receipt['total']) ?></td>
                                <td><?= date("F j, Y g:i A", strtotime($receipt['sale_date'])) ?></td>
                                <td>
                                    <button class="btn btn-primary btn-sm printReceipt" data-receipt="<?= $receipt['receipt_number'] ?>">
                                        <i class="fas fa-print"></i> Print
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- View Receipt Modal -->
<div class="modal fade" id="receiptModal" tabindex="-1" role="dialog" aria-labelledby="receiptModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <!-- Header -->
            <div class="modal-header">
                <h5 class="modal-title" id="receiptModalLabel">Receipt</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <!-- Body -->
            <div class="modal-body bg-light">
                <div id="receiptContent" class="p-3 border rounded bg-white shadow-sm">
                    <!-- Receipt content loaded via AJAX -->
                    <div class="text-center">Loading...</div>
                </div>
            </div>

            <!-- Footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="printReceiptBtn"><i class="fas fa-print"></i> Print</button>
                <button type="button" class="btn btn-light border" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php include_once 'footer.php'; ?>