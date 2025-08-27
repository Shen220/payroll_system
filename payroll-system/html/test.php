<label>Payroll Period:</label>
<select name="payroll_id" required>
    <option value="">-- Select Payroll Period --</option>
    <?php while ($row = $payroll_result1->fetch_assoc()): ?>
        <option value="<?= htmlspecialchars($payroll_data['payroll_id']) ?>"
            <?= ($payroll_data['payroll_id'] ?? '') == $row['payroll_id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($row['week']) ?>
        </option>
    <?php endwhile; ?>
</select>