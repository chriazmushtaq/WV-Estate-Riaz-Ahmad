<div class="container" style="max-width:960px;margin:30px auto;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">
    <div style="text-align:center;margin-bottom:30px;">
        <h2 style="font-weight:900;color:#0f172a;margin:0 0 6px 0;">📜 <?php echo __('Resident Billing Ledger & Statement'); ?></h2>
        <p style="color:#64748b;font-size:15px;margin:0;"><?php echo __('Search with your registered consumer phone or CNIC to see all past bills and payment receipts.'); ?></p>
    </div>

    <!-- Search Box -->
    <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:14px;padding:24px;box-shadow:0 10px 25px -5px rgba(0,0,0,0.05);margin-bottom:28px;">
        <form method="get" action="<?php echo $this->Html->url(array('action' => 'history')); ?>" style="display:flex;gap:12px;">
            <input type="text" name="q" value="<?php echo h($query); ?>" placeholder="<?php echo __('Enter Consumer Phone or CNIC...'); ?>" required style="flex:1;padding:12px 18px;font-size:15px;border:2px solid #cbd5e1;border-radius:10px;outline:none;font-weight:600;" />
            <button type="submit" style="background:#0284c7;color:#fff;font-weight:800;border:none;border-radius:10px;padding:12px 28px;cursor:pointer;font-size:15px;">
                🔍 <?php echo __('Search History'); ?>
            </button>
        </form>
    </div>

    <?php if(!empty($client)){ $c = $client['BillingClient']; ?>
        <!-- Client Profile -->
        <div style="background:#0f172a;color:#fff;border-radius:14px;padding:20px 24px;margin-bottom:24px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
            <div>
                <div style="font-size:11px;color:#94a3b8;text-transform:uppercase;font-weight:800;"><?php echo __('Resident Consumer'); ?></div>
                <h3 style="margin:4px 0;font-size:20px;font-weight:900;color:#38bdf8;"><?php echo h($c['name']); ?></h3>
                <div style="font-size:13px;color:#cbd5e1;"><?php echo __('Phone'); ?>: <?php echo h($c['phone']); ?> | <?php echo __('Address'); ?>: <?php echo h($c['address']); ?></div>
            </div>
            <div>
                <span style="background:rgba(255,255,255,0.1);padding:8px 16px;border-radius:10px;font-weight:800;font-size:14px;">
                    <?php echo count($bills); ?> <?php echo __('Invoices On Record'); ?>
                </span>
            </div>
        </div>

        <!-- Bills History Table -->
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;overflow:hidden;box-shadow:0 4px 14px rgba(0,0,0,0.05);">
            <table style="width:100%;border-collapse:collapse;font-size:13.5px;">
                <thead style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
                    <tr>
                        <th style="padding:12px 16px;text-align:left;"><?php echo __('Bill #'); ?></th>
                        <th style="padding:12px 16px;text-align:left;"><?php echo __('Period'); ?></th>
                        <th style="padding:12px 16px;text-align:left;"><?php echo __('Unit'); ?></th>
                        <th style="padding:12px 16px;text-align:right;"><?php echo __('Amount (PKR)'); ?></th>
                        <th style="padding:12px 16px;text-align:right;"><?php echo __('Paid (PKR)'); ?></th>
                        <th style="padding:12px 16px;text-align:center;"><?php echo __('Status'); ?></th>
                        <th style="padding:12px 16px;text-align:center;"><?php echo __('Invoice'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($bills)){ ?>
                        <tr><td colspan="7" style="padding:20px;text-align:center;color:#64748b;"><?php echo __('No bills on record.'); ?></td></tr>
                    <?php } else { foreach($bills as $bRow){ $b = $bRow['BillingBill']; $p = $bRow['BillingProperty']; ?>
                        <tr style="border-bottom:1px solid #f1f5f9;">
                            <td style="padding:12px 16px;font-weight:800;color:#0f172a;"><?php echo h($b['bill_number']); ?></td>
                            <td style="padding:12px 16px;"><span style="background:#f1f5f9;padding:4px 8px;border-radius:4px;font-weight:600;"><?php echo h($b['billing_period']); ?></span></td>
                            <td style="padding:12px 16px;"><?php echo h(!empty($p['title']) ? $p['title'] : '-'); ?></td>
                            <td style="padding:12px 16px;text-align:right;font-weight:800;color:#0284c7;"><?php echo number_format((float)$b['total_amount'], 2); ?></td>
                            <td style="padding:12px 16px;text-align:right;font-weight:800;color:#10b981;"><?php echo number_format((float)$b['paid_amount'], 2); ?></td>
                            <td style="padding:12px 16px;text-align:center;">
                                <?php if($b['payment_status'] === 'paid'){ ?>
                                    <span style="background:#dcfce7;color:#15803d;font-weight:800;font-size:11px;padding:3px 8px;border-radius:4px;">PAID</span>
                                <?php } elseif($b['payment_status'] === 'partial'){ ?>
                                    <span style="background:#fef3c7;color:#b45309;font-weight:800;font-size:11px;padding:3px 8px;border-radius:4px;">PARTIAL</span>
                                <?php } else { ?>
                                    <span style="background:#fee2e2;color:#b91c1c;font-weight:800;font-size:11px;padding:3px 8px;border-radius:4px;">UNPAID</span>
                                <?php } ?>
                            </td>
                            <td style="padding:12px 16px;text-align:center;">
                                <a href="<?php echo $this->Html->url(array('action' => 'view_bill', $b['bill_number'])); ?>" target="_blank" style="color:#0284c7;font-weight:800;text-decoration:none;">
                                    🖨️ View
                                </a>
                            </td>
                        </tr>
                    <?php } } ?>
                </tbody>
            </table>
        </div>
    <?php } ?>
</div>
