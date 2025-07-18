<?php
require '../require/check_auth.php';
checkAuth('admin');
require '../require/db.php';
require '../require/common.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: payment_method_list.php?error=Invalid payment method ID');
    exit;
}
$method_id = intval($_GET['id']);

$error = false;
$error_message = '';

// Fetch current payment method data
$res = $mysqli->query("SELECT * FROM payment_method WHERE id = $method_id");
if (!$res || $res->num_rows === 0) {
    header('Location: payment_method_list.php?error=Payment method not found');
    exit;
}
$row = $res->fetch_assoc();

$name = $row['name'];
$status = $row['status'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $mysqli->real_escape_string(trim($_POST['name']));
    $status = isset($_POST['status']) ? 1 : 0;

    if ($name === '') {
        $error = true;
        $error_message = 'ကျေးဇူးပြု၍ ငွေပေးချေနည်းလမ်း အမည်ထည့်ပါ။';
    } else {
        $sql = "UPDATE payment_method SET name='$name', status='$status' WHERE id=$method_id";
        $result = $mysqli->query($sql);
        if ($result) {
            header('Location: payment_method_list.php?success=Payment method updated successfully');
            exit;
        } else {
            $error = true;
            $error_message = 'ငွေပေးချေနည်းလမ်း ဖန်တီးရန် မအောင်မြင်ပါ။';
        }
    }
}
require '../layouts/header.php';
?>
<div class="content-body">
    <div class="container-fluid mt-3">
        <div class="card">
            <div class="card-body">
                <h3 class="text-center mb-2 text-info">ငွေပေး‌ချေမှုနည်းလမ်း အသစ်ပြင်ခြင်း</h3>
            </div>
            <?php if ($error && $error_message) { ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
            <?php } ?>
            <div class="card">
                <div class="card-body">
                    <form method="POST">
                        <div class="form-group">
                            <label for="name" class="form-label">အမည်</label>
                            <input type="text" name="name" id="name" class="form-control" value="<?= htmlspecialchars($name) ?>" required />
                        </div>
                        <div class="form-group">
                            <label><input type="checkbox" name="status" value="1" <?= $status ? 'checked' : '' ?> /> Active</label>
                        </div>
                        <button type="submit" class="btn btn-primary">တင်သွင်းပါ</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
<?php require '../layouts/footer.php'; ?>