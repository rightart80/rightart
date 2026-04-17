<div class="product-trust">

    <div class="free-ship-banner">
        <h3>FREE SHIPPING</h3>
        <p>In the UK</p>
        {* Calculate current date + 15 days *}
        {assign var="delivery_date" value="+15 days"|strtotime|date_format:"%b %e"}

        <p class="caps"><strong>Order now to receive by {$delivery_date}</strong></p>

    </div>

    <div class="copy-return">
        <span class="h3">Free Returns</span>

        <p>15-day Money Back<br>
            <a target="_blank" href="/returns-policy" title="Read our Returns Policy">Some Exclusions Apply </a>
        </p>

    </div>
</div>