<?php
require '../require/check_auth.php';
checkAuth('admin');
require '../require/db.php';
require '../require/common.php';
$success = isset($_GET['success']) ? $_GET['success'] : '';
$error = isset($_GET['error']) ? $_GET['error'] : '';
$sql = "SELECT ps.id, p.name as product_name, ps.qty, ps.total_price, ps.sale_date, pr.package_name as promotion_name, pr.percentage as promotion_percent FROM product_sales ps INNER JOIN products p ON ps.product_id = p.id LEFT JOIN promotions pr ON ps.promotion_id = pr.id ORDER BY ps.id DESC";
$sales = $mysqli->query($sql);
require '../layouts/header.php';


$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sql = "SELECT ps.id, p.name as product_name, ps.qty, ps.total_price, ps.sale_date, pr.package_name as promotion_name, pr.percentage as promotion_percent FROM product_sales ps INNER JOIN products p ON ps.product_id = p.id LEFT JOIN promotions pr ON ps.promotion_id = pr.id";
if ($search !== '') {
    $search_escaped = $mysqli->real_escape_string($search);
    $sql .= " WHERE  p.name LIKE '%$search_escaped%' OR ps.sale_date LIKE '%$search_escaped%' OR pr.package_name LIKE '%$search_escaped%'";
}
$sales = $mysqli->query($sql);

?>



<div class="content-body py-3">
    <div class="container-fluid">
        <div class="d-flex justify-content-between mb-3">
            <h3>ပစ္စည်းအရောင်းများစာရင်း</h3>
            <div class="">
                <a href="product_sale_create.php" class="btn btn-primary">ပစ္စည်းအရောင်းများစာရင်း ဖန်တီးရန်</a>
            </div>
        </div>

        <div class="col-12 mb-3">
            <form method="GET" class="form-inline d-flex justify-content-end">
                <input type="text" name="search" class="form-control mr-2" placeholder="Search by name or date" value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn btn-primary">Search</button>
            </form>
        </div>

        <div class="row">
            <div class="col-md-4 offset-md-8 col-sm-6 offset-sm-6">
                <?php if ($success !== '') { ?>
                    <div class="alert alert-success">
                        <?= $success ?>
                    </div>
                <?php } ?>
                <?php if ($error !== '') { ?>
                    <div class="alert alert-danger">
                        <?= $error ?>
                    </div>
                <?php } ?>
            </div>
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <table class="table table-hover table-sm">
                            <thead>
                                <tr>
                                    <th>စဉ်</th>
                                    <th>ပစ္စည်းအမည်</th>
                                    <th>အရေအတွက်</th>
                                    <th>ပစ္စည်းစျေးနှုန်းများ</th>
                                    <th>ပရိုမိုးရှင်း</th>
                                    <th>‌ရောင်းချသည့်ရက်စွဲ</th>
                                    <th>လုပ်ဆောင်မှု</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if ($sales && $sales->num_rows > 0) {
                                    $i = 1;
                                    while ($row = $sales->fetch_assoc()) { ?>
                                        <tr>
                                            <td><?= $i++ ?></td>
                                            <td><?= htmlspecialchars($row['product_name']) ?></td>
                                            <td><?= htmlspecialchars($row['qty']) ?></td>
                                            <td><?= htmlspecialchars($row['total_price']) ?></td>
                                            <td><?php
                                                if ($row['promotion_name']) {
                                                    echo htmlspecialchars($row['promotion_name']) . ' (' . htmlspecialchars($row['promotion_percent']) . '%)';
                                                } else {
                                                    echo '-';
                                                }
                                                ?></td>
                                            <td><?= htmlspecialchars($row['sale_date']) ?></td>
                                            <td>
                                                <a href="product_sale_edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-success edit_btn mx-2">ပြင်ဆင်ရန်</a>
                                            </td>
                                        </tr>
                                    <?php }
                                } else { ?>
                                    <tr>
                                        <td colspan="7" class="text-center">ပစ္စည်းအရောင်းများ ရှာမတွေ့ပါ</td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require '../layouts/footer.php'; ?>