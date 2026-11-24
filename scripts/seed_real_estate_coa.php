<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=whitevillasfinal;charset=utf8', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    function getRealEstateCoaCatalog() {
        return array(
            // 10000 ASSETS
            array('code'=>'10000', 'name'=>'ASSETS', 'parent'=>'', 'type'=>'asset', 'normal'=>'debit', 'cat'=>'Assets', 'header'=>1, 'ledger_type'=>'asset'),
            array('code'=>'11000', 'name'=>'Current Assets', 'parent'=>'10000', 'type'=>'asset', 'normal'=>'debit', 'cat'=>'Current Assets', 'header'=>1, 'ledger_type'=>'asset'),
            array('code'=>'11100', 'name'=>'Cash & Bank Balances', 'parent'=>'11000', 'type'=>'asset', 'normal'=>'debit', 'cat'=>'Cash & Bank', 'header'=>1, 'ledger_type'=>'bank'),
            array('code'=>'11110', 'name'=>'Cash in Hand (Main Safe)', 'parent'=>'11100', 'type'=>'asset', 'normal'=>'debit', 'cat'=>'Cash', 'header'=>0, 'ledger_type'=>'bank', 'field'=>'cash_closing_cash_ledger_id', 'alias'=>array('Cash-inhand', 'Cash In Hand')),
            array('code'=>'11120', 'name'=>'Petty Cash (Site Office)', 'parent'=>'11100', 'type'=>'asset', 'normal'=>'debit', 'cat'=>'Cash', 'header'=>0, 'ledger_type'=>'bank', 'alias'=>array('Paty Cash', 'Petty Cash')),
            array('code'=>'11130', 'name'=>'Primary Bank Account', 'parent'=>'11100', 'type'=>'asset', 'normal'=>'debit', 'cat'=>'Bank', 'header'=>0, 'ledger_type'=>'bank', 'field'=>'deal_receipt_online_ledger_id', 'alias'=>array('UBL Account Official', 'UBL old for houses', 'General Receipt Account')),
            array('code'=>'11140', 'name'=>'Secondary Bank Account', 'parent'=>'11100', 'type'=>'asset', 'normal'=>'debit', 'cat'=>'Bank', 'header'=>0, 'ledger_type'=>'bank'),
            array('code'=>'11200', 'name'=>'Accounts & Installments Receivable', 'parent'=>'11000', 'type'=>'asset', 'normal'=>'debit', 'cat'=>'Receivables', 'header'=>1, 'ledger_type'=>'asset'),
            array('code'=>'11210', 'name'=>'Plot / Property Installments Receivable', 'parent'=>'11200', 'type'=>'asset', 'normal'=>'debit', 'cat'=>'Receivables', 'header'=>0, 'ledger_type'=>'asset'),
            array('code'=>'11220', 'name'=>'Tenant Rental Receivables', 'parent'=>'11200', 'type'=>'asset', 'normal'=>'debit', 'cat'=>'Receivables', 'header'=>0, 'ledger_type'=>'asset'),
            array('code'=>'11230', 'name'=>'Society Maintenance & Utility Receivables', 'parent'=>'11200', 'type'=>'asset', 'normal'=>'debit', 'cat'=>'Receivables', 'header'=>0, 'ledger_type'=>'asset'),
            array('code'=>'11300', 'name'=>'Advances & Prepayments', 'parent'=>'11000', 'type'=>'asset', 'normal'=>'debit', 'cat'=>'Advances', 'header'=>1, 'ledger_type'=>'asset'),
            array('code'=>'11310', 'name'=>'Advance to Material Vendors', 'parent'=>'11300', 'type'=>'asset', 'normal'=>'debit', 'cat'=>'Advances', 'header'=>0, 'ledger_type'=>'asset'),
            array('code'=>'11320', 'name'=>'Advance to Subcontractors & Labour', 'parent'=>'11300', 'type'=>'asset', 'normal'=>'debit', 'cat'=>'Advances', 'header'=>0, 'ledger_type'=>'asset'),
            array('code'=>'11330', 'name'=>'Advance Income Tax / WHT Deducted', 'parent'=>'11300', 'type'=>'asset', 'normal'=>'debit', 'cat'=>'Tax', 'header'=>0, 'ledger_type'=>'asset'),
            array('code'=>'11400', 'name'=>'Project Inventory & Work in Progress', 'parent'=>'11000', 'type'=>'asset', 'normal'=>'debit', 'cat'=>'Inventory', 'header'=>1, 'ledger_type'=>'asset'),
            array('code'=>'11410', 'name'=>'Land Held for Development', 'parent'=>'11400', 'type'=>'asset', 'normal'=>'debit', 'cat'=>'Land Inventory', 'header'=>0, 'ledger_type'=>'asset'),
            array('code'=>'11420', 'name'=>'Construction Work-in-Progress (CWIP)', 'parent'=>'11400', 'type'=>'asset', 'normal'=>'debit', 'cat'=>'Construction WIP', 'header'=>0, 'ledger_type'=>'asset', 'field'=>'construction_wip_ledger_id', 'alias'=>array('Construction WIP')),
            array('code'=>'11430', 'name'=>'Construction Material Stock (Stores)', 'parent'=>'11400', 'type'=>'asset', 'normal'=>'debit', 'cat'=>'Stock Inventory', 'header'=>0, 'ledger_type'=>'asset'),
            array('code'=>'11440', 'name'=>'Finished Units Available for Sale', 'parent'=>'11400', 'type'=>'asset', 'normal'=>'debit', 'cat'=>'Finished Units', 'header'=>0, 'ledger_type'=>'asset'),
            array('code'=>'12000', 'name'=>'Non-Current & Fixed Assets', 'parent'=>'10000', 'type'=>'asset', 'normal'=>'debit', 'cat'=>'Fixed Assets', 'header'=>1, 'ledger_type'=>'asset'),
            array('code'=>'12100', 'name'=>'Head Office Premises & Buildings', 'parent'=>'12000', 'type'=>'asset', 'normal'=>'debit', 'cat'=>'Fixed Asset', 'header'=>0, 'ledger_type'=>'asset'),
            array('code'=>'12200', 'name'=>'Site Machinery & Heavy Equipment', 'parent'=>'12000', 'type'=>'asset', 'normal'=>'debit', 'cat'=>'Fixed Asset', 'header'=>0, 'ledger_type'=>'asset'),
            array('code'=>'12300', 'name'=>'Company Vehicles', 'parent'=>'12000', 'type'=>'asset', 'normal'=>'debit', 'cat'=>'Fixed Asset', 'header'=>0, 'ledger_type'=>'asset'),
            array('code'=>'12400', 'name'=>'Office Furniture, Computers & IT', 'parent'=>'12000', 'type'=>'asset', 'normal'=>'debit', 'cat'=>'Fixed Asset', 'header'=>0, 'ledger_type'=>'asset'),
            array('code'=>'12500', 'name'=>'Accumulated Depreciation', 'parent'=>'12000', 'type'=>'asset', 'normal'=>'credit', 'cat'=>'Contra Asset', 'header'=>0, 'ledger_type'=>'asset'),

            // 20000 LIABILITIES
            array('code'=>'20000', 'name'=>'LIABILITIES', 'parent'=>'', 'type'=>'liability', 'normal'=>'credit', 'cat'=>'Liabilities', 'header'=>1, 'ledger_type'=>'liability'),
            array('code'=>'21000', 'name'=>'Current Liabilities', 'parent'=>'20000', 'type'=>'liability', 'normal'=>'credit', 'cat'=>'Current Liabilities', 'header'=>1, 'ledger_type'=>'liability'),
            array('code'=>'21100', 'name'=>'Accounts Payable (Trade Creditors)', 'parent'=>'21000', 'type'=>'liability', 'normal'=>'credit', 'cat'=>'Payables', 'header'=>1, 'ledger_type'=>'liability'),
            array('code'=>'21110', 'name'=>'Material Vendors Accounts Payable', 'parent'=>'21100', 'type'=>'liability', 'normal'=>'credit', 'cat'=>'Payables', 'header'=>0, 'ledger_type'=>'liability', 'field'=>'vendor_payable_ledger_id', 'alias'=>array('Vendor Payable')),
            array('code'=>'21120', 'name'=>'Subcontractor & Civil Labour Payable', 'parent'=>'21100', 'type'=>'liability', 'normal'=>'credit', 'cat'=>'Payables', 'header'=>0, 'ledger_type'=>'liability'),
            array('code'=>'21130', 'name'=>'Retention Money Payable (Contractors)', 'parent'=>'21100', 'type'=>'liability', 'normal'=>'credit', 'cat'=>'Payables', 'header'=>0, 'ledger_type'=>'liability'),
            array('code'=>'21200', 'name'=>'Customer Advances & Unearned Revenue', 'parent'=>'21000', 'type'=>'liability', 'normal'=>'credit', 'cat'=>'Customer Advances', 'header'=>1, 'ledger_type'=>'liability'),
            array('code'=>'21210', 'name'=>'Customer Booking Advances (Unearned)', 'parent'=>'21200', 'type'=>'liability', 'normal'=>'credit', 'cat'=>'Customer Advances', 'header'=>0, 'ledger_type'=>'liability'),
            array('code'=>'21220', 'name'=>'Installments Received in Advance', 'parent'=>'21200', 'type'=>'liability', 'normal'=>'credit', 'cat'=>'Customer Advances', 'header'=>0, 'ledger_type'=>'liability'),
            array('code'=>'21230', 'name'=>'Customer Refund Payable (Cancelled Deals)', 'parent'=>'21200', 'type'=>'liability', 'normal'=>'credit', 'cat'=>'Refunds', 'header'=>0, 'ledger_type'=>'liability', 'field'=>'refund_payable_ledger_id', 'alias'=>array('Refund Payable')),
            array('code'=>'21300', 'name'=>'Accrued & Other Liabilities', 'parent'=>'21000', 'type'=>'liability', 'normal'=>'credit', 'cat'=>'Accrued Liabilities', 'header'=>1, 'ledger_type'=>'liability'),
            array('code'=>'21310', 'name'=>'Sales Agent & Broker Commission Payable', 'parent'=>'21300', 'type'=>'liability', 'normal'=>'credit', 'cat'=>'Commission Payable', 'header'=>0, 'ledger_type'=>'liability', 'alias'=>array('Agent Commission Payable')),
            array('code'=>'21320', 'name'=>'Accrued Salaries & Wages Payable', 'parent'=>'21300', 'type'=>'liability', 'normal'=>'credit', 'cat'=>'Payroll Payable', 'header'=>0, 'ledger_type'=>'liability'),
            array('code'=>'21330', 'name'=>'Tenant Security Deposits (Refundable)', 'parent'=>'21300', 'type'=>'liability', 'normal'=>'credit', 'cat'=>'Rental Deposits', 'header'=>0, 'ledger_type'=>'liability'),
            array('code'=>'21340', 'name'=>'Utility & Maintenance Charges Payable', 'parent'=>'21300', 'type'=>'liability', 'normal'=>'credit', 'cat'=>'Utilities Payable', 'header'=>0, 'ledger_type'=>'liability'),
            array('code'=>'21400', 'name'=>'Statutory & Tax Liabilities', 'parent'=>'21000', 'type'=>'liability', 'normal'=>'credit', 'cat'=>'Tax Liabilities', 'header'=>1, 'ledger_type'=>'liability'),
            array('code'=>'21410', 'name'=>'Withholding Tax Payable', 'parent'=>'21400', 'type'=>'liability', 'normal'=>'credit', 'cat'=>'Tax', 'header'=>0, 'ledger_type'=>'liability', 'field'=>'tax_payable_ledger_id', 'alias'=>array('Tax Payable')),
            array('code'=>'21420', 'name'=>'Sales Tax / Provincial Tax Payable', 'parent'=>'21400', 'type'=>'liability', 'normal'=>'credit', 'cat'=>'Tax', 'header'=>0, 'ledger_type'=>'liability'),
            array('code'=>'22000', 'name'=>'Long-Term Liabilities', 'parent'=>'20000', 'type'=>'liability', 'normal'=>'credit', 'cat'=>'Long Term Liabilities', 'header'=>1, 'ledger_type'=>'liability'),
            array('code'=>'22100', 'name'=>'Bank Project Financing & Mortgages', 'parent'=>'22000', 'type'=>'liability', 'normal'=>'credit', 'cat'=>'Bank Loans', 'header'=>0, 'ledger_type'=>'liability'),
            array('code'=>'22200', 'name'=>'Investor Long-Term Capital Loans', 'parent'=>'22000', 'type'=>'liability', 'normal'=>'credit', 'cat'=>'Investor Financing', 'header'=>0, 'ledger_type'=>'liability'),

            // 30000 EQUITY / CAPITAL
            array('code'=>'30000', 'name'=>'EQUITY & CAPITAL', 'parent'=>'', 'type'=>'equity', 'normal'=>'credit', 'cat'=>'Equity', 'header'=>1, 'ledger_type'=>'equity'),
            array('code'=>'31000', 'name'=>'Owner / Partner Capital', 'parent'=>'30000', 'type'=>'equity', 'normal'=>'credit', 'cat'=>'Capital', 'header'=>1, 'ledger_type'=>'equity'),
            array('code'=>'31100', 'name'=>'Owner / Partner Capital Account', 'parent'=>'31000', 'type'=>'equity', 'normal'=>'credit', 'cat'=>'Capital', 'header'=>0, 'ledger_type'=>'equity', 'alias'=>array('Owner', 'Sajjad Sabir')),
            array('code'=>'31200', 'name'=>'Partner Drawings & Personal Withdrawals', 'parent'=>'31000', 'type'=>'equity', 'normal'=>'debit', 'cat'=>'Drawings', 'header'=>0, 'ledger_type'=>'equity'),
            array('code'=>'32000', 'name'=>'Reserves & Retained Earnings', 'parent'=>'30000', 'type'=>'equity', 'normal'=>'credit', 'cat'=>'Retained Earnings', 'header'=>1, 'ledger_type'=>'equity'),
            array('code'=>'32100', 'name'=>'Retained Earnings (Prior Years Profit/Loss)', 'parent'=>'32000', 'type'=>'equity', 'normal'=>'credit', 'cat'=>'Retained Earnings', 'header'=>0, 'ledger_type'=>'equity'),
            array('code'=>'32200', 'name'=>'Current Year Profit / (Loss)', 'parent'=>'32000', 'type'=>'equity', 'normal'=>'credit', 'cat'=>'Current Earnings', 'header'=>0, 'ledger_type'=>'equity'),

            // 40000 REVENUE / INCOME
            array('code'=>'40000', 'name'=>'REVENUE & OPERATING INCOME', 'parent'=>'', 'type'=>'income', 'normal'=>'credit', 'cat'=>'Revenue', 'header'=>1, 'ledger_type'=>'income'),
            array('code'=>'41000', 'name'=>'Property Sales & Recognized Revenue', 'parent'=>'40000', 'type'=>'income', 'normal'=>'credit', 'cat'=>'Sales Revenue', 'header'=>1, 'ledger_type'=>'income'),
            array('code'=>'41100', 'name'=>'Plot / Property Sales Revenue', 'parent'=>'41000', 'type'=>'income', 'normal'=>'credit', 'cat'=>'Sales Revenue', 'header'=>0, 'ledger_type'=>'income'),
            array('code'=>'41200', 'name'=>'Booking Income', 'parent'=>'41000', 'type'=>'income', 'normal'=>'credit', 'cat'=>'Sales Revenue', 'header'=>0, 'ledger_type'=>'income', 'field'=>'booking_income_ledger_id', 'alias'=>array('Booking Income')),
            array('code'=>'41300', 'name'=>'Installment Income', 'parent'=>'41000', 'type'=>'income', 'normal'=>'credit', 'cat'=>'Sales Revenue', 'header'=>0, 'ledger_type'=>'income', 'field'=>'installment_income_ledger_id', 'alias'=>array('Installment Income')),
            array('code'=>'41400', 'name'=>'Deal Additional Charges Income', 'parent'=>'41000', 'type'=>'income', 'normal'=>'credit', 'cat'=>'Sales Revenue', 'header'=>0, 'ledger_type'=>'income', 'field'=>'deal_charges_income_ledger_id', 'alias'=>array('Deal Charges Income')),
            array('code'=>'42000', 'name'=>'Real Estate Service & Operational Income', 'parent'=>'40000', 'type'=>'income', 'normal'=>'credit', 'cat'=>'Operational Income', 'header'=>1, 'ledger_type'=>'income'),
            array('code'=>'42100', 'name'=>'Plot Ownership Transfer Fee Income', 'parent'=>'42000', 'type'=>'income', 'normal'=>'credit', 'cat'=>'Transfer Fees', 'header'=>0, 'ledger_type'=>'income', 'field'=>'transfer_fee_income_ledger_id', 'alias'=>array('Transfer Fee Income')),
            array('code'=>'42200', 'name'=>'Demarcation, Map & Possession Fee Income', 'parent'=>'42000', 'type'=>'income', 'normal'=>'credit', 'cat'=>'Documentation Fees', 'header'=>0, 'ledger_type'=>'income'),
            array('code'=>'42300', 'name'=>'Cancellation & Forfeiture Fee Income', 'parent'=>'42000', 'type'=>'income', 'normal'=>'credit', 'cat'=>'Cancellation Income', 'header'=>0, 'ledger_type'=>'income', 'field'=>'cancellation_forfeiture_income_ledger_id', 'alias'=>array('Cancellation / Forfeiture Income')),
            array('code'=>'42400', 'name'=>'Late Payment Surcharge / Penalty Income', 'parent'=>'42000', 'type'=>'income', 'normal'=>'credit', 'cat'=>'Surcharge Income', 'header'=>0, 'ledger_type'=>'income', 'field'=>'late_fee_income_ledger_id', 'alias'=>array('Late Fee Income')),
            array('code'=>'42500', 'name'=>'Rental Property Income', 'parent'=>'42000', 'type'=>'income', 'normal'=>'credit', 'cat'=>'Rental Income', 'header'=>0, 'ledger_type'=>'income', 'alias'=>array('Rental-Income')),
            array('code'=>'42600', 'name'=>'Rental Adjustment as Installment', 'parent'=>'42000', 'type'=>'income', 'normal'=>'credit', 'cat'=>'Rental Adjustment', 'header'=>0, 'ledger_type'=>'income', 'field'=>'rental_adjustment_income_ledger_id', 'alias'=>array('Rental Adjustment as Installment')),
            array('code'=>'42700', 'name'=>'Agency & Brokerage Commission Income', 'parent'=>'42000', 'type'=>'income', 'normal'=>'credit', 'cat'=>'Brokerage Income', 'header'=>0, 'ledger_type'=>'income'),
            array('code'=>'42800', 'name'=>'Utility & Maintenance Billing Revenue', 'parent'=>'42000', 'type'=>'income', 'normal'=>'credit', 'cat'=>'Utility Revenue', 'header'=>0, 'ledger_type'=>'income', 'field'=>'billing_income_ledger_id', 'alias'=>array('Billing Utility Revenue')),
            array('code'=>'42900', 'name'=>'Miscellaneous Receipts & Other Income', 'parent'=>'42000', 'type'=>'income', 'normal'=>'credit', 'cat'=>'Other Income', 'header'=>0, 'ledger_type'=>'income'),

            // 50000 COST OF SALES / DIRECT DEVELOPMENT
            array('code'=>'50000', 'name'=>'COST OF SALES & DEVELOPMENT', 'parent'=>'', 'type'=>'expense', 'normal'=>'debit', 'cat'=>'Cost of Sales', 'header'=>1, 'ledger_type'=>'expense'),
            array('code'=>'51000', 'name'=>'Direct Property Development Cost', 'parent'=>'50000', 'type'=>'expense', 'normal'=>'debit', 'cat'=>'Cost of Sales', 'header'=>1, 'ledger_type'=>'expense'),
            array('code'=>'51100', 'name'=>'Cost of Plots / Units Sold (COGS from CWIP)', 'parent'=>'51000', 'type'=>'expense', 'normal'=>'debit', 'cat'=>'Cost of Sales', 'header'=>0, 'ledger_type'=>'expense'),
            array('code'=>'51200', 'name'=>'Direct Land Development Cost Allocated', 'parent'=>'51000', 'type'=>'expense', 'normal'=>'debit', 'cat'=>'Cost of Sales', 'header'=>0, 'ledger_type'=>'expense'),
            array('code'=>'51300', 'name'=>'Direct Site Infrastructure & Roads Allocated', 'parent'=>'51000', 'type'=>'expense', 'normal'=>'debit', 'cat'=>'Cost of Sales', 'header'=>0, 'ledger_type'=>'expense'),

            // 60000 OPERATING & ADMINISTRATIVE EXPENSES
            array('code'=>'60000', 'name'=>'OPERATING & ADMINISTRATIVE EXPENSES', 'parent'=>'', 'type'=>'expense', 'normal'=>'debit', 'cat'=>'Operating Expenses', 'header'=>1, 'ledger_type'=>'expense'),
            array('code'=>'61000', 'name'=>'Staff Salaries & Payroll Expenses', 'parent'=>'60000', 'type'=>'expense', 'normal'=>'debit', 'cat'=>'Payroll', 'header'=>1, 'ledger_type'=>'expense'),
            array('code'=>'61100', 'name'=>'Staff Salaries & Allowances', 'parent'=>'61000', 'type'=>'expense', 'normal'=>'debit', 'cat'=>'Payroll', 'header'=>0, 'ledger_type'=>'expense', 'field'=>'payroll_payment_expense_ledger_id', 'alias'=>array('HR Payroll', 'Staff Salary')),
            array('code'=>'61200', 'name'=>'Staff Overtime & Bonuses', 'parent'=>'61000', 'type'=>'expense', 'normal'=>'debit', 'cat'=>'Payroll', 'header'=>0, 'ledger_type'=>'expense'),
            array('code'=>'61300', 'name'=>'Employee Welfare, Medical & Tea', 'parent'=>'61000', 'type'=>'expense', 'normal'=>'debit', 'cat'=>'Office', 'header'=>0, 'ledger_type'=>'expense', 'alias'=>array('Tea', 'Grocery')),
            array('code'=>'62000', 'name'=>'Sales, Marketing & Agency Commissions', 'parent'=>'60000', 'type'=>'expense', 'normal'=>'debit', 'cat'=>'Marketing', 'header'=>1, 'ledger_type'=>'expense'),
            array('code'=>'62100', 'name'=>'Sales Agent & Broker Commissions Expense', 'parent'=>'62000', 'type'=>'expense', 'normal'=>'debit', 'cat'=>'Commission Expense', 'header'=>0, 'ledger_type'=>'expense', 'field'=>'agent_commission_expense_ledger_id', 'alias'=>array('Agent Commission Expense', 'Paid Agents Commission')),
            array('code'=>'62200', 'name'=>'Digital Marketing, Social Media & Ads', 'parent'=>'62000', 'type'=>'expense', 'normal'=>'debit', 'cat'=>'Marketing', 'header'=>0, 'ledger_type'=>'expense', 'alias'=>array('Marketing')),
            array('code'=>'62300', 'name'=>'Billboards, Signage & Print Media', 'parent'=>'62000', 'type'=>'expense', 'normal'=>'debit', 'cat'=>'Marketing', 'header'=>0, 'ledger_type'=>'expense'),
            array('code'=>'62400', 'name'=>'Client Hospitality & Expo Events', 'parent'=>'62000', 'type'=>'expense', 'normal'=>'debit', 'cat'=>'Marketing', 'header'=>0, 'ledger_type'=>'expense'),
            array('code'=>'62500', 'name'=>'Discounts & Waivers Allowed', 'parent'=>'62000', 'type'=>'expense', 'normal'=>'debit', 'cat'=>'Discounts', 'header'=>0, 'ledger_type'=>'expense', 'field'=>'discount_waiver_ledger_id', 'alias'=>array('Discount / Waiver')),
            array('code'=>'63000', 'name'=>'General Office & Administrative Expenses', 'parent'=>'60000', 'type'=>'expense', 'normal'=>'debit', 'cat'=>'Office Expenses', 'header'=>1, 'ledger_type'=>'expense'),
            array('code'=>'63100', 'name'=>'Office Rent', 'parent'=>'63000', 'type'=>'expense', 'normal'=>'debit', 'cat'=>'Rent', 'header'=>0, 'ledger_type'=>'expense', 'alias'=>array('Rental-Payments', 'Office Rent')),
            array('code'=>'63200', 'name'=>'Office Electricity, Gas & Water Bills', 'parent'=>'63000', 'type'=>'expense', 'normal'=>'debit', 'cat'=>'Utilities', 'header'=>0, 'ledger_type'=>'expense', 'alias'=>array('Electricity Bill')),
            array('code'=>'63300', 'name'=>'Internet, Wifi & Telephone Bills', 'parent'=>'63000', 'type'=>'expense', 'normal'=>'debit', 'cat'=>'Office', 'header'=>0, 'ledger_type'=>'expense', 'alias'=>array('Wifi Bill', 'Mobile Packges')),
            array('code'=>'63400', 'name'=>'Office Stationery, Printing & Postage', 'parent'=>'63000', 'type'=>'expense', 'normal'=>'debit', 'cat'=>'Office', 'header'=>0, 'ledger_type'=>'expense', 'alias'=>array('Office')),
            array('code'=>'63500', 'name'=>'Vehicle Fuel, Bike & Travel Expense', 'parent'=>'63000', 'type'=>'expense', 'normal'=>'debit', 'cat'=>'Vehicle', 'header'=>0, 'ledger_type'=>'expense', 'alias'=>array('Car Expanse', 'Bike')),
            array('code'=>'63600', 'name'=>'Legal, Regulatory & Government Fees', 'parent'=>'63000', 'type'=>'expense', 'normal'=>'debit', 'cat'=>'Legal', 'header'=>0, 'ledger_type'=>'expense'),
            array('code'=>'63700', 'name'=>'Depreciation Expense', 'parent'=>'63000', 'type'=>'expense', 'normal'=>'debit', 'cat'=>'Depreciation', 'header'=>0, 'ledger_type'=>'expense'),
            array('code'=>'63800', 'name'=>'Bank Charges & Financial Deductions', 'parent'=>'63000', 'type'=>'expense', 'normal'=>'debit', 'cat'=>'Bank Charges', 'header'=>0, 'ledger_type'=>'expense'),
            array('code'=>'63900', 'name'=>'General Office Maintenance & Repairs', 'parent'=>'63000', 'type'=>'expense', 'normal'=>'debit', 'cat'=>'General Expense', 'header'=>0, 'ledger_type'=>'expense', 'field'=>'purchase_payment_expense_ledger_id', 'alias'=>array('General Expense Account', 'Garbage', 'Chairty', 'Others'))
        );
    }

    function provisionProjectCoa($pdo, $projectId) {
        $projectId = (int)$projectId;
        if ($projectId <= 0) return false;

        $catalog = getRealEstateCoaCatalog();
        $coaIdMap = array(); // code => id

        // 1. Seed ardaccounts_chart_of_accounts
        $insCoa = $pdo->prepare("INSERT INTO `ardaccounts_chart_of_accounts` 
            (`project_id`, `account_code`, `account_name`, `parent_code`, `account_type`, `normal_balance`, `category`, `is_header`, `is_system_default`, `is_active`, `created`, `modified`)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, 1, NOW(), NOW())
            ON DUPLICATE KEY UPDATE 
            `account_name`=VALUES(`account_name`),
            `parent_code`=VALUES(`parent_code`),
            `account_type`=VALUES(`account_type`),
            `normal_balance`=VALUES(`normal_balance`),
            `category`=VALUES(`category`),
            `is_header`=VALUES(`is_header`),
            `modified`=NOW()");

        foreach ($catalog as $item) {
            $insCoa->execute(array(
                $projectId,
                $item['code'],
                $item['name'],
                $item['parent'],
                $item['type'],
                $item['normal'],
                $item['cat'],
                $item['header']
            ));
        }

        // Fetch COA map
        $stmt = $pdo->prepare("SELECT id, account_code FROM `ardaccounts_chart_of_accounts` WHERE `project_id` = ?");
        $stmt->execute(array($projectId));
        while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $coaIdMap[$r['account_code']] = (int)$r['id'];
        }

        // 2. Fetch existing ledgers for this project
        $existStmt = $pdo->prepare("SELECT id, name, type, category, account_code, coa_id FROM `ardaccounts_ledgers` WHERE `project_id` = ? AND `is_trashed` = 0");
        $existStmt->execute(array($projectId));
        $existingLedgers = $existStmt->fetchAll(PDO::FETCH_ASSOC);

        $matchedLedgerIds = array();
        $settingsFieldMap = array();

        // Pass A: Match existing ledgers by account_code or exact/alias name
        foreach ($catalog as $item) {
            if ($item['header']) continue; // only posting ledgers
            $code = $item['code'];
            $coaId = isset($coaIdMap[$code]) ? $coaIdMap[$code] : 0;
            $matchedId = 0;

            foreach ($existingLedgers as $el) {
                if (in_array((int)$el['id'], $matchedLedgerIds, true)) continue;

                // Match by code
                if (!empty($el['account_code']) && $el['account_code'] === $code) {
                    $matchedId = (int)$el['id'];
                    break;
                }
                // Match by exact name
                if (strcasecmp(trim($el['name']), trim($item['name'])) === 0) {
                    $matchedId = (int)$el['id'];
                    break;
                }
                // Match by alias
                if (!empty($item['alias'])) {
                    foreach ($item['alias'] as $alias) {
                        if (stripos(trim($el['name']), $alias) !== false) {
                            $matchedId = (int)$el['id'];
                            break 2;
                        }
                    }
                }
            }

            if ($matchedId > 0) {
                $matchedLedgerIds[] = $matchedId;
                // Update ledger with proper COA metadata
                $upd = $pdo->prepare("UPDATE `ardaccounts_ledgers` SET 
                    `account_code` = ?,
                    `account_type` = ?,
                    `normal_balance` = ?,
                    `coa_id` = ?,
                    `modified` = NOW()
                    WHERE `id` = ?");
                $upd->execute(array($code, $item['type'], $item['normal'], $coaId, $matchedId));

                if (!empty($item['field'])) {
                    $settingsFieldMap[$item['field']] = $matchedId;
                }
            } else {
                // Insert posting ledger if not existing
                $insLedger = $pdo->prepare("INSERT INTO `ardaccounts_ledgers` 
                    (`project_id`, `name`, `type`, `category`, `account_code`, `account_type`, `normal_balance`, `coa_id`, `balance`, `is_trashed`, `created`, `modified`)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0.00, 0, NOW(), NOW())");
                $insLedger->execute(array(
                    $projectId,
                    $item['name'],
                    $item['ledger_type'],
                    $item['cat'],
                    $code,
                    $item['type'],
                    $item['normal'],
                    $coaId
                ));
                $newLedgerId = (int)$pdo->lastInsertId();
                if (!empty($item['field'])) {
                    $settingsFieldMap[$item['field']] = $newLedgerId;
                }
            }
        }

        // 3. Update ardaccounts_settings with mapped ledger IDs
        if (!empty($settingsFieldMap)) {
            $setParts = array();
            $setVals = array();
            foreach ($settingsFieldMap as $f => $val) {
                $setParts[] = "`$f` = ?";
                $setVals[] = $val;
            }
            $setVals[] = $projectId;
            $setSql = "UPDATE `ardaccounts_settings` SET " . implode(', ', $setParts) . ", `modified` = NOW() WHERE `project_id` = ?";
            $updSettings = $pdo->prepare($setSql);
            $updSettings->execute($setVals);
        }

        echo " - Provisioned Real Estate COA for Project {$projectId}: (" . count($catalog) . " heads)\n";
        return true;
    }

    echo "Running provisioning for existing projects...\n";
    $pStmt = $pdo->query("SELECT id, name FROM projects");
    while ($p = $pStmt->fetch(PDO::FETCH_ASSOC)) {
        echo "Processing Project ID: " . $p['id'] . " (" . $p['name'] . ")\n";
        provisionProjectCoa($pdo, (int)$p['id']);
    }
    echo "COA SEEDING COMPLETE!\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
