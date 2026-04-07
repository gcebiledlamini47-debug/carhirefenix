<?php
require_once 'db.php';
$pageTitle = 'Terms & Conditions';
?>
<?php include 'header.php'; ?>

<div class="page-hero">
    <div class="container">
        <h1>Terms &amp; Conditions</h1>
        <p>Please read our rental agreement terms carefully</p>
    </div>
</div>

<section class="section bg-white">
    <div class="container" style="max-width:860px">
        <?php
        $terms = [
            ['Age & License', 'Drivers must be 25 years or older and hold a valid driver\'s license. International licenses are accepted for short-term rentals.'],
            ['Fuel Policy', 'Vehicles are provided with a full tank and must be returned full. Any fuel shortfall will be charged at prevailing pump rates plus a service fee.'],
            ['Kilometre Allowance', 'Each rental includes 200 free kilometres per day. Kilometres driven beyond this allowance are charged at the agreed excess rate per km.'],
            ['Damage & Responsibility', 'It is the customer\'s responsibility to inspect the vehicle and report any existing damage before driving off. Chargeable damage includes tyres, stone chips to bodywork and glass.'],
            ['Refundable Deposit', 'A security deposit is required at the time of rental. This covers potential damage, excess kilometre charges, or unpaid fuel. The deposit is fully refunded upon satisfactory return of the vehicle.'],
            ['Cancellation Policy', 'Cancellations must be made at least 24 hours before the scheduled rental period. Late cancellations or no-shows may incur a cancellation fee.'],
            ['Vehicle Cleanliness', 'Vehicles must be returned in a reasonably clean condition. Excessive soiling — interior or exterior — may attract a cleaning surcharge.'],
            ['Late Returns', 'Late returns will be charged at the daily rate, pro-rated per hour, unless prior arrangements have been made with our office.'],
            ['Cross-Border Travel', 'Cross-border travel to South Africa and neighbouring countries requires prior written approval from Fenix Car Hire. Additional documentation and fees may apply.'],
            ['Accidents & Breakdowns', 'In the event of an accident or breakdown, the renter must contact Fenix Car Hire immediately. The customer is responsible for obtaining a police report for any accident.'],
            ['Prohibited Use', 'Vehicles may not be used for racing, off-road driving (unless specifically authorised), transporting illegal goods, or any unlawful purpose.'],
            ['Insurance', 'Basic insurance is included. The renter remains liable for the excess amount specified at the time of rental in the event of an accident or damage claim.'],
        ];
        foreach($terms as $term):
            $title = $term[0]; $desc = $term[1];
        ?>
        <div class="term-item">
            <div class="term-icon">✓</div>
            <div>
                <h3><?php echo $title ?></h3>
                <p><?php echo $desc ?></p>
            </div>
        </div>
        <?php endforeach; ?>
        <div class="terms-footer-note">
            By renting a vehicle from Fenix Car Hire, you agree to all the above terms and conditions. For any queries, contact us at <strong>reception@fenix.co.sz</strong> or call <strong>(+268) 2422 1045</strong>.
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>