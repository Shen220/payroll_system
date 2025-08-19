
    <?php if (!isset($value)) { die("No employee data."); } ?>
    <form class="ppayslip-form">

        <input class="ppayslip-input" type="hidden" name="employee_id" value="<?= htmlspecialchars($value['employee_id']) ?>">
            <div class="ppayslip-restaurant">
                                    <p>Date: <?= date("F j, Y") ?></p>
                <h2 class="ppayslip-text">AI Korean Buffet Restaurant</h2>
                <h2 class="ppayslip-text">MH del pilar Burnham Legarda road, Baguio City, Philippines</h2>
            </div>

            <div class="ppayslip-basic_info">
                <div class="ppayslip-first">
                    <p class="ppayslip-info"><strong class="ppayslip-strong">ID:</strong> <?= htmlspecialchars($value['employee_id']) ?></p>
                    <p class="ppayslip-p"><strong class="ppayslip-strong">Name:</strong> <?= mb_strtoupper(htmlspecialchars($value['last_name'])) ?>, <?= htmlspecialchars($value['first_name']) ?></p>
                </div>
                <div class="ppayslip-second">
                    <p class="ppayslip-info"><strong class="ppayslip-strong">Position:</strong> <?= htmlspecialchars($value['position']) ?></p>
                    <p class="ppayslip-p"><strong class="ppayslip-strong">Status:</strong> <?= htmlspecialchars($value['status']) ?></p>
                </div>

            </div>
    </form>
