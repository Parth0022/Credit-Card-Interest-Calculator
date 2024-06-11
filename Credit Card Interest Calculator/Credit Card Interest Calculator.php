<?php
/*
Plugin Name: Credit Card Interest Calculator
Description: Calculate credit card interest rates with bank selection from CSV file.
Version: 1.0
Author: Partha Santosh
*/

// Define shortcode for the calculator
function credit_card_interest_calculator_shortcode() {
    ob_start();
    ?>
    <form method="post" action="">
        <label for="balance">Balance:</label>
        <input type="text" id="balance" name="balance" required><br><br>
        
        <label for="apr">APR (%):</label>
        <input type="text" id="apr" name="apr" required><br><br>
        
        <label for="bank">Select Bank:</label>
        <select id="bank" name="bank" required>
            <?php
            // Populate the dropdown with banks from the CSV file
            $banks = get_banks_from_csv();
            foreach ($banks as $bank) {
                echo "<option value='$bank'>$bank</option>";
            }
            ?>
        </select><br><br>
        
        <input type="submit" name="submit" value="Calculate">
    </form>
    <?php
    return ob_get_clean();
}
add_shortcode('credit_card_calculator', 'credit_card_interest_calculator_shortcode');

// Function to get banks from CSV file
function get_banks_from_csv() {
    $banks = [];
    $file = plugin_dir_path(__FILE__) . 'bank_interest_rates.csv';
    if (($handle = fopen($file, 'r')) !== false) {
        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            $banks[] = $data[0];
        }
        fclose($handle);
    }
    return $banks;
}

// Function to calculate interest
function calculate_interest($balance, $apr, $bank) {
    // Load CSV and find interest rate for selected bank
    $file = plugin_dir_path(__FILE__) . 'bank_interest_rates.csv';
    if (($handle = fopen($file, 'r')) !== false) {
        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            if ($data[0] === $bank) {
                $interest_rate = $data[1];
                break;
            }
        }
        fclose($handle);
    }
    // Calculate interest
    $monthly_interest_rate = $interest_rate / 12 / 100;
    $monthly_interest = $balance * $monthly_interest_rate;
    return $monthly_interest;
}

// Hook to handle form submission
function handle_form_submission() {
    if (isset($_POST['submit'])) {
        $balance = floatval($_POST['balance']);
        $apr = floatval($_POST['apr']);
        $bank = $_POST['bank'];
        
        $interest = calculate_interest($balance, $apr, $bank);
        display_interest_results($interest);
    }
}
add_action('init', 'handle_form_submission');

// Function to display the results
function display_interest_results($interest) {
    echo "<h3>Monthly Interest: $" . number_format($interest, 2) . "</h3>";
}
?>
