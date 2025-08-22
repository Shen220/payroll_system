<form method="POST" action="add.php" class="add-form">
    <fieldset class="add-fieldset">
    <!-- Employee Info -->
        <label>Last Name:</label>
        <input type="text" name="last_name" required>

        <label>First Name:</label>
        <input type="text" name="first_name" required>

        <label>Position:</label>
        <input type="text" name="position" required>

        <label>Status:</label>
        <select name="status" required>
            <option value="Permanent">Permanent</option>
            <option value="On-Call">On-Call</option>
        </select>

        <label>Board & Lodging:</label>
        <select name="board_lodging" required onchange="toggleLodgingAddress(this.value)">
            <option value="Yes" >Yes</option>
            <option value="No" selected>No</option>
        </select>

        <div id="add-lodging_input" style="display:none;">
            <label>Lodging Address:</label>
            <input class="add-input" type="text" name="lodging_address">
        </div>

        <label>Food Allowance:</label>
        <select name="food_allowance" required>
            <option value="Full">Full</option>
            <option value="Partial">Partial</option>
            <option value="None">None</option>
        </select>

    <!-- Employee Rates -->
   <label>Daily Minimum Wage:</label>
        <span>₱</span>
        <input type="number" name="w1_daily_minimum_wage" value="470" required>

    <label>Sunday Rest Day:</label>
        <span>₱</span>
        <input type="number" name="w2_sunday_rest_day" value="611">

    <label>Legal Holiday:</label>
        <span>₱</span>
        <input type="number" name="w3_legal_holiday" value="940">

    <label>Special Holiday:</label>
        <span>₱</span>
        <input type="number" name="w4_special_holiday" value="611">

    <label>Regular Overtime Per Hour:</label>
        <span>₱</span>
        <input type="number" name="w5_regular_overtime_perhour" value="73.44">

    <label>Special Overtime Per Hour:</label>
        <span>₱</span>
        <input type="number" name="w6_special_overtime_perhour" value="76.38">

    <label>Special Holiday Overtime Per Hour:</label>
        <span>₱</span>
        <input type="number" name="w7_special_holiday_overtime_perhour" value="99.29">

    <label>Regular Holiday Overtime Per Hour:</label>
        <span>₱</span>
        <input type="number" name="w8_regular_holiday_overtime_perhour" value="152.75">

    <label>Cater:</label>
        <span>₱</span>
        <input type="number" name="w9_cater" value="1000">

    </fieldset>

    <div id="add-submit_div">
        <input type="submit" value="Add Employee" class="add-update-btn">
    </div>
</form>
<!-- Code is clean/ css is not -->
