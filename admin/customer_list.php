<?php

require "../require/common_function.php";
require '../require/db.php';
require '../require/common.php';
$success = isset($_GET['success']) ? $_GET['success'] : '';
$error = isset($_GET['error']) ? $_GET['error'] : '';
$res = selectData('customers', $mysqli, "", "*", "ORDER BY created_at DESC");




$delete_id = isset($_GET['delete_id']) ?  $_GET['delete_id'] : '';
if ($delete_id !== '') {
    $res = deleteData('customers', $mysqli, "id=$delete_id");
    if ($res) {
        $url = $admin_base_url . "customer_list.php?success=Delete customer Success";
        header("Location: $url");
    }
}
require '../layouts/header.php';
?>
<div class="content-body">
    <div class="container-fluid">
        <div class="d-flex justify-content-between">
            <h3>ဖောက်သည်စာရင်း</h3>
            <div class="">
                <a href="<?= $admin_base_url . 'customer_create.php' ?>" class="btn btn-primary">
                    ဖောက်သည်အသစ်ဖန်တီးရန်
                </a>
            </div>
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
                                <tr class="text-center">
                                    <th class="">နံပါတ်</th>
                                    <th class="">အမည်</th>
                                    <th class="">ဆက်သွယ်ရန်ဖုန်း</th>
                                    <th class="">စကားဝှက်</th>
                                    <th class="">လုပ်ဆောင်မှု</th>

                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($res->num_rows > 0) {
                                    $i = 1;
                                    while ($row = $res->fetch_assoc()) { ?>
                                        <tr class="text-center">
                                            <td><?= $i++ ?></td>
                                            <td><?= $row['name'] ?></td>
                                            <td><?= $row['phone'] ?></td>
                                            <td><?= $row['password'] ?></td>


                                            <td>
                                                <a href="./customer_edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-success edit_btn mx-2">ပြင်ဆင်ရန်</a>
                                                <a href="./appointment_create.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary appointment_btn mx-2">အချိန်ချိန်းဆိုမှုစာရင်း</a>
                                                <button data-id=" <?= $row['id'] ?>" class="btn btn-sm btn-danger delete_btn">ဖျက်ရန်</button>
                                            </td>
                                        </tr>
                                <?php }
                                } ?>
                            </tbody>
                        </table>
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
    $(document).ready(function() {
        $('.delete_btn').click(function() {
            const id = $(this).data('id')
            Swal.fire({
                title: 'Are you sure?',
                text: "This action cannot be undone.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'customer_list.php?delete_id=' + id
                }
            });
        })
    })
</script>
<?php
require '../layouts/footer.php';
?>