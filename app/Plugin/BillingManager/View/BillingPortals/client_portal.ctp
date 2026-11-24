<div class="container" style="max-width:960px;margin:30px auto;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">
    
    <?php if(empty($client)){ ?>
        <!-- Login Card -->
        <div style="max-width:440px;margin:60px auto;background:#ffffff;border:1px solid #e2e8f0;border-radius:16px;padding:32px;box-shadow:0 20px 35px -10px rgba(0,0,0,0.08);text-align:center;">
            <div style="font-size:36px;margin-bottom:12px;">🏠</div>
            <h2 style="font-weight:900;color:#0f172a;margin:0 0 8px 0;"><?php echo __('White Villas Resident Portal'); ?></h2>
            <p style="color:#64748b;font-size:14px;margin-bottom:24px;"><?php echo __('Enter your registered mobile number to manage your utility accounts and bills.'); ?></p>

            <?php echo $this->Session->flash(); ?>

            <form method="post" action="<?php echo $this->Html->url(array('action' => 'client_portal')); ?>">
                <div style="text-align:left;margin-bottom:18px;">
                    <label style="font-size:12px;font-weight:800;color:#334155;text-transform:uppercase;"><?php echo __('Consumer Phone Number'); ?></label>
                    <input type="text" name="data[Portal][phone]" placeholder="03001234567" required style="width:100%;box-sizing:border-box;padding:12px 16px;border:2px solid #cbd5e1;border-radius:10px;font-size:15px;font-weight:700;margin-top:6px;outline:none;" />
                </div>
                <button type="submit" style="width:100%;background:#0284c7;color:#fff;font-weight:900;padding:14px;border:none;border-radius:10px;font-size:15px;cursor:pointer;box-shadow:0 4px 12px rgba(2,132,199,0.3);">
                    <?php echo __('Access Resident Portal'); ?> &rarr;
                </button>
            </form>
        </div>
    <?php } else { $c = $client['BillingClient']; $props = $client['BillingProperty']; ?>
        <!-- Resident Dashboard -->
        <div style="background:#0f172a;color:#fff;border-radius:16px;padding:24px;margin-bottom:24px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px;">
            <div>
                <div style="font-size:11px;color:#38bdf8;text-transform:uppercase;font-weight:800;letter-spacing:0.5px;"><?php echo __('Resident Portal'); ?></div>
                <h2 style="margin:4px 0;font-size:24px;font-weight:900;color:#ffffff;"><?php echo __('Welcome, ') . h($c['name']); ?></h2>
                <div style="font-size:13px;color:#94a3b8;"><?php echo h($c['phone']); ?> | <?php echo h($c['address']); ?></div>
            </div>
            <div>
                <a href="<?php echo $this->Html->url(array('action' => 'check_bill')); ?>" style="background:#0284c7;color:#fff;font-weight:800;padding:10px 20px;border-radius:8px;text-decoration:none;font-size:13px;">
                    🔍 <?php echo __('Quick Bill Search'); ?>
                </a>
            </div>
        </div>

        <?php echo $this->Session->flash(); ?>

        <!-- Pending Unpaid Bills Alert -->
        <?php if(!empty($unpaidBills)){ ?>
            <div style="background:#fff1f2;border:1px solid #fecdd3;border-radius:14px;padding:20px;margin-bottom:24px;">
                <h3 style="margin:0 0 12px 0;font-size:17px;font-weight:900;color:#9f1239;">⚠️ <?php echo __('You have pending utility invoices:'); ?></h3>
                <?php foreach($unpaidBills as $ub){ $ubB = $ub['BillingBill']; ?>
                    <div style="background:#fff;border:1px solid #ffe4e6;border-radius:10px;padding:14px 18px;margin-bottom:10px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
                        <div>
                            <strong><?php echo h($ubB['bill_number']); ?></strong> (<?php echo h($ubB['billing_period']); ?>)
                            <div style="font-size:12px;color:#64748b;"><?php echo __('Due Date'); ?>: <?php echo date('d-M-Y', strtotime((string)$ubB['due_date'])); ?></div>
                        </div>
                        <div style="display:flex;align-items:center;gap:12px;">
                            <strong style="color:#e11d48;font-size:18px;">PKR <?php echo number_format((float)$ubB['balance_amount'], 2); ?></strong>
                            <a href="<?php echo $this->Html->url(array('action' => 'view_bill', $ubB['bill_number'])); ?>" target="_blank" style="background:#0f172a;color:#fff;font-weight:800;padding:8px 16px;border-radius:8px;text-decoration:none;font-size:12px;">
                                🖨️ <?php echo __('View Bill'); ?>
                            </a>
                        </div>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>

        <!-- All Invoices on Record -->
        <h3 style="font-weight:900;color:#0f172a;margin-bottom:14px;">🧾 <?php echo __('All Invoices'); ?></h3>
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;overflow:hidden;box-shadow:0 4px 14px rgba(0,0,0,0.05);">
            <table style="width:100%;border-collapse:collapse;font-size:13.5px;">
                <thead style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
                    <tr>
                        <th style="padding:12px 16px;text-align:left;"><?php echo __('Bill #'); ?></th>
                        <th style="padding:12px 16px;text-align:left;"><?php echo __('Month'); ?></th>
                        <th style="padding:12px 16px;text-align:right;"><?php echo __('Amount'); ?></th>
                        <th style="padding:12px 16px;text-align:right;"><?php echo __('Paid'); ?></th>
                        <th style="padding:12px 16px;text-align:center;"><?php echo __('Status'); ?></th>
                        <th style="padding:12px 16px;text-align:center;"><?php echo __('Action'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($allBills)){ ?>
                        <tr><td colspan="6" style="padding:20px;text-align:center;color:#64748b;"><?php echo __('No invoices found.'); ?></td></tr>
                    <?php } else { foreach($allBills as $ab){ $b = $ab['BillingBill']; ?>
                        <tr style="border-bottom:1px solid #f1f5f9;">
                            <td style="padding:12px 16px;font-weight:800;"><?php echo h($b['bill_number']); ?></td>
                            <td style="padding:12px 16px;"><span style="background:#f1f5f9;padding:4px 8px;border-radius:4px;font-weight:600;"><?php echo h($b['billing_period']); ?></span></td>
                            <td style="padding:12px 16px;text-align:right;font-weight:800;color:#0284c7;">PKR <?php echo number_format((float)$b['total_amount'], 2); ?></td>
                            <td style="padding:12px 16px;text-align:right;font-weight:800;color:#10b981;">PKR <?php echo number_format((float)$b['paid_amount'], 2); ?></td>
                            <td style="padding:12px 16px;text-align:center;">
                                <?php if($b['payment_status'] === 'paid'){ ?>
                                    <span style="background:#dcfce7;color:#15803d;font-weight:800;font-size:11px;padding:3px 8px;border-radius:4px;">PAID</span>
                                <?php } else { ?>
                                    <span style="background:#fee2e2;color:#b91c1c;font-weight:800;font-size:11px;padding:3px 8px;border-radius:4px;">UNPAID</span>
                                <?php } ?>
                            </td>
                            <td style="padding:12px 16px;text-align:center;">
                                <a href="<?php echo $this->Html->url(array('action' => 'view_bill', $b['bill_number'])); ?>" target="_blank" style="color:#0284c7;font-weight:800;text-decoration:none;">
                                    🖨️ <?php echo __('Print'); ?>
                                </a>
                            </td>
                        </tr>
                    <?php } } ?>
                </tbody>
            </table>
        </div>
    <?php } ?>

</div>
