<?php
require '../require/check_auth.php';
checkAuth('admin');
require '../require/db.php';
require '../require/common.php';

// Generate a unique token for this form session
if (!isset($_SESSION['payment_form_token'])) {
    $_SESSION['payment_form_token'] = uniqid();
}

$error = false;
$error_message = '';
$appointment_id_error = $amount_error = $payment_method_error = $payment_date_error = '';
$appointment_id = $amount = $payment_method = $payment_date = '';

// Check if appointment ID is passed via GET
$filter_appointment_id = isset($_GET['id']) && is_numeric($_GET['id']) ? intval($_GET['id']) : '';

// Always fetch all available appointments for the dropdown
if(isset($_GET['id'])) {
$appointments = $mysqli->query("SELECT a.id, c.name as customer_name, s.name as service_name,
 a.appointment_date, a.appointment_time, s.price as service_price FROM appointments a 
 INNER JOIN customers c ON a.customer_id = c.id INNER JOIN services s ON a.service_id = s.id 
 WHERE a.status = 1 AND a.id = $filter_appointment_id");
} 
// Pre-select the appointment if ID is passed
if ($filter_appointment_id) {
    $appointment_id = $filter_appointment_id;
}

// Fetch payment methods
$payment_methods = $mysqli->query("SELECT id, name FROM payment_method WHERE status = 1 ORDER BY name ASC");
$payment_method_id_error = '';
$payment_method_id = '';

if (isset($_POST['form_sub']) && $_POST['form_sub'] == '1') {
    // Check if this form has already been processed
    if (isset($_POST['form_token']) && $_POST['form_token'] === $_SESSION['payment_form_token']) {
        $appointment_id = $mysqli->real_escape_string($_POST['appointment_id']);
        $amount = $mysqli->real_escape_string($_POST['amount']);
        $payment_method_id = isset($_POST['payment_method_id']) ? $mysqli->real_escape_string($_POST['payment_method_id']) : '';
        $payment_date = date("Y-m-d");
        // Validation
        if ($appointment_id === '' || !is_numeric($appointment_id)) {
            $error = true;
            $appointment_id_error = "ချိန်းဆိုချိန်ကို ရွေးချယ်ပါ။";
        }
        if ($amount === '' || !is_numeric($amount) || $amount <= 0) {
            $error = true;
            $amount_error = "ကျေးဇူးပြုပြီး မှန်ကန်သောငွေပမာဏကို ဖြည့်ပါ။";
        }
        if (empty($payment_method_id) || !is_numeric($payment_method_id)) {
            $error = true;
            $payment_method_id_error = "ကျေးဇူးပြုပြီး ငွေပေးချေမှုနည်းလမ်းကို ရွေးချယ်ပါ။";
        }

        if (!$error) {
            // Check if payment already exists for this appointment
            $check_sql = "SELECT id FROM payments WHERE appointment_id = '$appointment_id'";
            $check_result = $mysqli->query($check_sql);

            if ($check_result && $check_result->num_rows > 0) {
                $error = true;
                $error_message = "ဤချိန်းဆိုမှုအတွက် ငွေပေးချေမှု ရှိပြီးဖြစ်ပါသည်။";
            } else {
                $sql = "INSERT INTO payments (appointment_id, amount, payment_method_id, payment_date) VALUES ('$appointment_id', '$amount', '$payment_method_id', '$payment_date')";
                $result = $mysqli->query($sql);
                if ($result) {
                    // Generate new token to prevent resubmission
                    $_SESSION['payment_form_token'] = uniqid();

                    $url = $admin_base_url . 'payment_list.php?success=Payment created successfully';
                    header("Location: $url");
                    exit;
                } else {
                    $error = true;
                    $error_message = "ငွေပေးချေမှု ဖန်တီးခြင်း မအောင်မြင်ပါ။";
                }
            }
        }

        // Clear the token after processing to prevent resubmission
        unset($_SESSION['payment_form_token']);
    } else {
        $error = true;
        $url = $admin_base_url . 'payment_list.php?success';
        header("Location: $url");
        exit;
    }
}
require '../layouts/header.php';
?>

<!--**********************************
            Content body start
        ***********************************-->
<div class="content-body">
    <div class="container-fluid mt-3">
        <div class="d-flex justify-content-between">
            <h3 class="text-center mb-5 text-info">ငွေပေးချေမှုစာရင်း ဖန်တီးရန်</h3>
            <div class="">
                <a href="<?= $admin_base_url . 'payment_list.php' ?>" class="btn btn-dark">
                    နောက်သို့
                </a>
            </div>
        </div>
        <div class="d-flex justify-content-center">
            <div class="col-md-6 col-sm-10 col-12">
                <?php if ($error && $error_message) { ?>
                    <div class="alert alert-danger">
                        <?= $error_message ?>
                    </div>
                <?php } ?>
                <div class="card">
                    <div class="card-body">
                        <form action="<?= $admin_base_url ?>payment_create.php" method="POST">
                            <div class="form-group mb-2">
                                <label for="appointment_id" class="form-label">အချိန်ချိန်းဆိုမှု</label>
                                <select name="appointment_id" class="form-control" id="appointment_id">
                                    <!-- <option value="">အချိန်ချိန်းဆိုမှု ရွေးချယ်ရန်</option> -->
                                    <?php
                                    if ($appointments && $appointments->num_rows > 0) {
                                        while ($row = $appointments->fetch_assoc()) {
                                            $selected = ($appointment_id == $row['id']) ? 'selected' : '';
                                            $time12 = '';
                                            if (isset($row['appointment_time'])) {
                                                $time12 = date('g:i A', strtotime($row['appointment_time']));
                                            }
                                            $service_price = isset($row['service_price']) ? $row['service_price'] : '';
                                            echo "<option value='{$row['id']}' $selected data-price='{$service_price}'>({$row['id']}) {$row['customer_name']} - {$row['service_name']} ({$row['appointment_date']} , {$time12})</option>";
                                        }
                                    } else {
                                        echo '<option value="">No appointments available</option>';
                                    } ?>
                                </select>
                                <?php if ($error && $appointment_id_error) { ?>
                                    <span class="text-danger"><?= $appointment_id_error ?></span>
                                <?php } ?>
                            </div>
                            <div class="form-group mb-2">
                                <label for="amount" class="form-label">ငွေပမာဏ</label>
                                <input type="number" name="amount" class="form-control" id="amount" value="<?php
                                                                                                            if ($filter_appointment_id && $appointments && $appointments->num_rows > 0) {
                                                                                                                $appointments->data_seek(0);
                                                                                                                $row = $appointments->fetch_assoc();
                                                                                                                echo htmlspecialchars($row['service_price']);
                                                                                                            } else {
                                                                                                                echo htmlspecialchars($amount);
                                                                                                            }
                                                                                                            ?>" min="1" />
                                <?php if ($error && $amount_error) { ?>
                                    <span class="text-danger"><?= $amount_error ?></span>
                                <?php } ?>
                            </div>
                            <div class="form-group mb-2">
                                <label for="payment_method_id" class="form-label">ငွေပေးချေမှုနည်းလမ်း</label>
                                <select name="payment_method_id" class="form-control" id="payment_method_id" required>
                                    <option value="">ငွေပေးချေမှုနည်းလမ်း ရွေးချယ်ရန်</option>
                                    <?php if ($payment_methods && $payment_methods->num_rows > 0) {
                                        while ($row = $payment_methods->fetch_assoc()) {
                                            $selected = ($payment_method_id == $row['id']) ? 'selected' : '';
                                            echo "<option value='{$row['id']}' $selected>{$row['name']}</option>";
                                        }
                                    } else {
                                        echo '<option value="">No payment methods available</option>';
                                    } ?>
                                </select>
                                <?php if ($error && $payment_method_id_error) { ?>
                                    <span class="text-danger"><?= $payment_method_id_error ?></span>
                                <?php } ?>
                            </div>
                            <input type="hidden" name="form_sub" value="1" />
                            <input type="hidden" name="form_token" value="<?= $_SESSION['payment_form_token'] ?>" />
                            <button type="submit" class="btn btn-primary w-100">အသစ်ထပ်တိုးရန်</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- #/ container -->
</div>
<!--**********************************
            Content body end
        ***********************************-->

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var appointmentSelect = document.getElementById('appointment_id');
        var amountInput = document.getElementById('amount');
        if (appointmentSelect && amountInput) {
            appointmentSelect.addEventListener('change', function() {
                var selected = appointmentSelect.options[appointmentSelect.selectedIndex];
                var price = selected.getAttribute('data-price');
                if (price) {
                    amountInput.value = price;
                } else {
                    amountInput.value = '';
                }
            });
        }
    });
</script>

<?php
require '../layouts/footer.php';
?>