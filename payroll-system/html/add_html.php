<form method="POST" action="add.php" class="add-form">
    <fieldset class="add-fieldset">
        <div class="add-form-group">
            <label class="add-label" for="last_name">Last Name:</label>
            <input type="text" id="add-name" name="last_name" required>
        </div>

        <div class="add-form-group">
            <label class="add-label" for="first_name">First Name:</label>
            <input type="text" id="add-name" name="first_name" required>
        </div>

        <div class="add-form-group">
            <label class="add-label" for="position">Position:</label>
            <input type="text" id="add-name" name="position" required>
        </div>

        <div class="add-form-group">
            <label class="add-label" for="status">Status:</label>
            <select id="add-status" name="status" required>
                <option value="Permanent">Permanent</option>
                <option value="On-Call">On-Call</option>
            </select>
        </div>

        <div class="add-form-group">
            <label class="add-label" for="board_lodging">Board & Lodging:</label>
            <select id="add-board_lodging" name="board_lodging" required onchange="toggleLodgingAddress(this.value)">
                <option value="Yes">Yes</option>
                <option value="No" selected>No</option>
            </select>
        </div>

        <div id="add-lodging_address_wrapper" class="form-group" style="display:none;">
            <label class="add-label" for="lodging_address">Lodging Address:</label>
            <input type="text" id="add-lodging_address" name="lodging_address">
        </div>

        <div class="add-form-group">
            <label class="add-label" for="food_allowance">Food Allowance:</label>
            <select id="add-food_allowance" name="food_allowance" required>
                <option value="Full">Full</option>
                <option value="Partial">Partial</option>
                <option value="None">None</option>
            </select>
        </div>

        
    </fieldset>

    <fieldset class="add-fieldset">
        <h5 class="add-h5">Employee Rates</h5>

        <div class="add-form-group">
            <label class="add-label" for="rate_1_daily_minimum_wage">Daily Minimum Wage:</label>
            <span class="add-span">₱</span>
            <input class="add-for_num" type="number" id="rate_1_daily_minimum_wage" name="rate_1_daily_minimum_wage" value="470" required>
        </div>

        <div class="add-form-group">
            <label class="add-label" for="rate_2_sunday_rest_day">Sunday Rest Day:</label>
            <span class="add-span">₱</span>
            <input class="add-for_num" type="number" id="rate_2_sunday_rest_day" name="rate_2_sunday_rest_day" value="611">
        </div>

        <div class="add-form-group">
            <label class="add-label" for="rate_3_legal_holiday">Legal Holiday:</label>
            <span class="add-span">₱</span>
            <input class="add-for_num" type="number" id="rate_3_legal_holiday" name="rate_3_legal_holiday" value="940">
        </div>

        <div class="add-form-group">
            <label class="add-label" for="rate_4_special_holiday">Special Holiday:</label>
            <span class="add-span">₱</span>
            <input class="add-for_num" type="number" id="rate_4_special_holiday" name="rate_4_special_holiday" value="611">
        </div>

        <div class="add-form-group">
            <label class="add-label" for="rate_5_regular_overtime_perhour">Regular Overtime Per Hour:</label>
            <span class="add-span">₱</span>
            <input class="add-for_num" type="number" id="rate_5_regular_overtime_perhour" name="rate_5_regular_overtime_perhour" value="73.44">
        </div>

        <div class="add-form-group">
            <label class="add-label" for="rate_6_special_overtime_perhour">Special Overtime Per Hour:</label>
            <span class="add-span">₱</span>
            <input class="add-for_num" type="number" id="rate_6_special_overtime_perhour" name="rate_6_special_overtime_perhour" value="76.38">
        </div>

        <div class="add-form-group">
            <label class="add-label" for="rate_7_special_holiday_overtime_perhour">Special Holiday Overtime Per Hour:</label>
            <span class="add-span">₱</span>
            <input class="add-for_num" type="number" id="rate_7_special_holiday_overtime_perhour" name="rate_7_special_holiday_overtime_perhour" value="99.29">
        </div>

        <div class="add-form-group">
            <label class="add-label" for="rate_8_regular_holiday_overtime_perhour">Regular Holiday Overtime Per Hour:</label>
            <span class="add-span">₱</span>
            <input class="add-for_num" type="number" id="rate_8_regular_holiday_overtime_perhour" name="rate_8_regular_holiday_overtime_perhour" value="152.75">
        </div>

        <div class="add-form-group">
            <label class="add-label" for="rate_9_cater">Cater:</label>
            <span class="add-span">₱</span>
            <input class="add-for_num" type="number" id="rate_9_cater" name="rate_9_cater" value="1000">
        </div>
    </fieldset>

    <div class="add-form-actions">
        <input type="submit" value="Add Employee" class="add-update-btn">
    </div>
</form>

<script>
function toggleLodgingAddress(value) {
    document.getElementById('lodging_address_wrapper').style.display = (value === 'Yes') ? 'block' : 'none';
}
</script>