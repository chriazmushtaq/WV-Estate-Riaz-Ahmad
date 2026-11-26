<?php
$this->Js->JqueryEngine->jQueryObject = 'jQuery';
$this->Paginator->options(array(
    'update' => '#resultDiv',
    'evalScripts' => true,
));

$favCount = !empty($fProperty) ? count($fProperty) : 0;
$totalRecords = 0;
try {
    $totalRecords = (int)$this->Paginator->counter('{:count}');
} catch (Exception $e) {
    $totalRecords = $favCount;
}

$availableCount = 0;
$soldCount = 0;
if (!empty($fProperty)) {
    foreach ($fProperty as $pItem) {
        $st = !empty($pItem['Property']['status']) ? strtolower($pItem['Property']['status']) : '';
        if ($st === 'sold') {
            $soldCount++;
        } else {
            $availableCount++;
        }
    }
}
?>
<style type="text/css">
.wv-fav-page {
    background: #f4f7fb;
    margin: -10px -5px 0;
    padding: 14px;
    min-height: 85vh;
}
.wv-fav-page * {
    box-sizing: border-box;
}
.wv-fav-hero {
    background: linear-gradient(135deg, #07152a 0%, #102746 70%, #ff0a46 160%);
    border-radius: 12px;
    box-shadow: 0 14px 30px rgba(15, 23, 42, 0.12);
    color: #ffffff;
    margin-bottom: 16px;
    overflow: hidden;
    padding: 22px 26px;
    position: relative;
}
.wv-fav-hero-inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
    position: relative;
    z-index: 2;
}
.wv-fav-kicker {
    color: #7cf9ff;
    font-size: 11px;
    font-weight: 900;
    letter-spacing: 0.5px;
    margin-bottom: 5px;
    text-transform: uppercase;
    display: flex;
    align-items: center;
    gap: 6px;
}
.wv-fav-hero h1 {
    color: #ffffff !important;
    font-size: 26px;
    font-weight: 900;
    line-height: 1.15;
    margin: 0 0 6px;
}
.wv-fav-hero p {
    color: #e2e8f0 !important;
    font-size: 13px;
    font-weight: 500;
    line-height: 1.45;
    margin: 0;
    max-width: 650px;
}
.wv-fav-hero-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}
.wv-fav-hero-actions .btn {
    border: 0;
    border-radius: 10px;
    font-size: 12px;
    font-weight: 800;
    padding: 10px 18px;
    text-transform: uppercase;
    color: #ffffff !important;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.18);
    transition: all 0.2s ease;
}
.wv-fav-hero-actions .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.25);
}
.wv-fav-hero-actions .btn-primary {
    background: linear-gradient(135deg, #ff0a46, #dc2626) !important;
}

/* Stats Row */
.wv-fav-stats {
    display: grid;
    gap: 12px;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    margin-bottom: 18px;
}
.wv-fav-stat {
    background: #ffffff;
    border: 1px solid #dfe9f5;
    border-radius: 12px;
    box-shadow: 0 6px 18px rgba(15, 23, 42, 0.05);
    padding: 14px 18px;
    position: relative;
    overflow: hidden;
}
.wv-fav-stat:before {
    background: #ff0a46;
    border-radius: 999px;
    content: "";
    height: 48px;
    opacity: 0.08;
    position: absolute;
    right: -12px;
    top: -12px;
    width: 48px;
}
.wv-fav-stat span {
    color: #64748b;
    display: block;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.5px;
    margin-bottom: 4px;
    text-transform: uppercase;
}
.wv-fav-stat strong {
    color: #07152a;
    display: block;
    font-size: 22px;
    font-weight: 900;
    line-height: 1.15;
}

/* Listing Box Modern Client Portal Theme */
.wv-fav-grid {
    margin-bottom: 20px;
}
.wv-fav-page .listing-box {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    box-shadow: 0 6px 20px rgba(15, 23, 42, 0.06);
    margin-bottom: 24px;
    overflow: hidden;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.wv-fav-page .listing-box:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 28px rgba(15, 23, 42, 0.12);
}
.wv-fav-page .listing-box-image {
    background-position: center;
    background-repeat: no-repeat;
    background-size: cover;
    height: 190px;
    position: relative;
}
.wv-fav-page .listing-box-image-label {
    border-radius: 6px;
    font-size: 10px;
    font-weight: 900;
    letter-spacing: 0.5px;
    padding: 5px 10px;
    position: absolute;
    left: 12px;
    top: 12px;
    text-transform: uppercase;
}
.wv-fav-page .listing-box-image-links {
    position: absolute;
    right: 12px;
    bottom: 12px;
    display: flex;
    gap: 6px;
}
.wv-fav-page .listing-box-image-links a,
.wv-fav-page .listing-box-image-links span {
    background: rgba(7, 21, 42, 0.85);
    border-radius: 8px;
    color: #ffffff !important;
    display: inline-block;
    font-size: 11px;
    font-weight: 700;
    padding: 6px 10px;
    backdrop-filter: blur(4px);
    transition: background 0.2s;
}
.wv-fav-page .listing-box-image-links a:hover {
    background: #ff0a46;
}
.wv-fav-page .listing-box-title {
    background: #ffffff;
    border-bottom: 1px solid #f1f5f9;
    padding: 14px 16px;
}
.wv-fav-page .listing-box-title h2 {
    font-size: 16px;
    font-weight: 800;
    line-height: 1.3;
    margin: 0 0 6px;
}
.wv-fav-page .listing-box-title h2 a {
    color: #07152a !important;
    text-decoration: none;
}
.wv-fav-page .listing-box-title h2 a:hover {
    color: #ff0a46 !important;
}
.wv-fav-page .listing-box-title h3 {
    color: #ff0a46 !important;
    font-size: 17px;
    font-weight: 900;
    margin: 0;
}
.wv-fav-page .listing-box-content {
    background: #ffffff;
    padding: 12px 16px;
}
.wv-fav-page .listing-box-content dl {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 6px 12px;
    margin: 0;
    font-size: 12px;
}
.wv-fav-page .listing-box-content dt {
    color: #64748b;
    font-weight: 700;
}
.wv-fav-page .listing-box-content dd {
    color: #0f172a;
    font-weight: 800;
    text-align: right;
    margin: 0;
}

/* Empty State Card */
.wv-fav-empty {
    background: #ffffff;
    border: 1px dashed #cbd5e1;
    border-radius: 14px;
    box-shadow: 0 4px 16px rgba(15, 23, 42, 0.04);
    margin: 20px 0;
    padding: 48px 24px;
    text-align: center;
}
.wv-fav-empty-icon {
    align-items: center;
    background: #fff1f2;
    border-radius: 999px;
    color: #ff0a46;
    display: inline-flex;
    font-size: 36px;
    height: 80px;
    justify-content: center;
    margin-bottom: 16px;
    width: 80px;
}
.wv-fav-empty h3 {
    color: #07152a;
    font-size: 19px;
    font-weight: 800;
    margin: 0 0 8px;
}
.wv-fav-empty p {
    color: #64748b;
    font-size: 13.5px;
    margin: 0 auto 20px;
    max-width: 480px;
}
.wv-fav-empty .btn {
    border-radius: 10px;
    font-size: 12px;
    font-weight: 800;
    padding: 10px 22px;
    text-transform: uppercase;
}

/* Pagination container */
.wv-fav-pagination-bar {
    background: #ffffff;
    border: 1px solid #dfe9f5;
    border-radius: 10px;
    padding: 10px 16px;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
@media (max-width: 768px) {
    .wv-fav-stats {
        grid-template-columns: 1fr;
    }
    .wv-fav-hero h1 {
        font-size: 20px;
    }
}
</style>

<div id="resultDiv" class="wv-fav-page">
    <!-- Hero Header -->
    <div class="wv-fav-hero">
        <div class="wv-fav-hero-inner">
            <div class="wv-fav-hero-text">
                <span class="wv-fav-kicker"><i class="fa fa-heart"></i> <?php echo __('Portal Shortlist'); ?></span>
                <h1><?php echo __('Favourite Properties'); ?></h1>
                <p><?php echo __('Your saved properties and shortlisted units for quick comparison, investment review, and direct booking.'); ?></p>
            </div>
            <div class="wv-fav-hero-actions">
                <a href="<?php echo $this->Html->url(array('controller' => 'Properties', 'action' => 'index')); ?>" class="btn btn-primary">
                    <i class="fa fa-search"></i>&nbsp; <?php echo __('Browse Properties'); ?>
                </a>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="wv-fav-stats">
        <div class="wv-fav-stat">
            <span><i class="fa fa-heart-o"></i> <?php echo __('Total Shortlisted'); ?></span>
            <strong><?php echo (int)$totalRecords; ?></strong>
        </div>
        <div class="wv-fav-stat">
            <span><i class="fa fa-check-circle-o"></i> <?php echo __('Available on Market'); ?></span>
            <strong><?php echo (int)$availableCount; ?></strong>
        </div>
        <div class="wv-fav-stat">
            <span><i class="fa fa-building-o"></i> <?php echo __('Booked / Under Deal'); ?></span>
            <strong><?php echo (int)$soldCount; ?></strong>
        </div>
    </div>

    <?php echo $this->Session->flash(); ?>

    <?php if ($searchRecord === 'Yes' && !empty($fProperty)): ?>
        <div class="wv-fav-pagination-bar">
            <?php echo $this->element('pagination', array('IsSearch' => 'No', 'IsDropdown' => 'No')); ?>
        </div>

        <div class="row wv-fav-grid">
            <?php foreach ($fProperty as $post): ?>
                <?php
                $this->Function->gridProperty($record, $post, $currency, '<div class="col-xs-12 col-sm-6 col-md-4 col-lg-3">');
                echo $record;
                ?>
            <?php endforeach; ?>
        </div>

        <div class="wv-fav-pagination-bar">
            <?php echo $this->element('pagination', array('IsSearch' => 'No', 'IsDropdown' => 'No')); ?>
        </div>
    <?php else: ?>
        <div class="wv-fav-empty">
            <div class="wv-fav-empty-icon">
                <i class="fa fa-heart-o"></i>
            </div>
            <h3><?php echo __('No Favourite Properties Yet'); ?></h3>
            <p><?php echo __('You have not added any properties to your favourites list yet. Explore available residential and commercial plots to shortlist them here.'); ?></p>
            <a href="<?php echo $this->Html->url(array('controller' => 'Properties', 'action' => 'index')); ?>" class="btn btn-primary">
                <i class="fa fa-search"></i>&nbsp; <?php echo __('Explore Properties'); ?>
            </a>
        </div>
    <?php endif; ?>
</div>
