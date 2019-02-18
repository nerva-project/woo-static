<textarea id="clipboardTextarea" style="width:0;height:0;position:absolute;top:0;left:0;"></textarea>
<script>
	function setTextInClipboard(inputId){
		var inputElement = document.getElementById(inputId);
		var textarea = document.getElementById('clipboardTextarea');
		textarea.value = inputElement.value;
		textarea.select();
		try {
			document.execCommand('copy');
		} catch (err) {
		}

	}
</script>
<script>window.ajaxurl = '<?php echo $ajaxurl ?>';</script>
<script>window.orderId = '<?php echo $order_id ?>';</script>
<script>
	var finish_count=0;
	function update() {
		jQuery(document).ready(function () {
			jQuery.post(
				ajaxurl+'?_d='+Date.now(),
				{
					'action': 'nerva_gateway_ajax_reload',
					'order_id': window.orderId
				},
				function (response) {
					if(typeof response === 'string')
						response = JSON.parse(response);
					
					jQuery('#nerva_gateway_payment_wait').hide();
					jQuery('#nerva_gateway_payment_process').hide();
					jQuery('#nerva_gateway_payment_success').hide();
					
					if(response.confirmed === true) {
						
						if(finish_count>0){
							clearInterval(intervalRefreshStatus);
							document.location.reload(true);
							
						}
						
					}

					if(response.confirmed === true) {
						jQuery('#nerva_gateway_payment_success').show();
						clearInterval(intervalRefreshStatus);
						
						
					}else if(response.paid=== null && response.currentConfirmations==null){
						finish_count++;
						
						jQuery('#nerva_gateway_payment_wait').show();
						jQuery('#xnv_amount').val(response.amount);
						
					}else if(response.paid!=null || response.currentConfirmations!=null){
						jQuery('#nerva_gateway_payment_process').show();
					
						jQuery('#nerva_gateway .count_currentConfirmations').html(response.currentConfirmations);
						jQuery('#nerva_gateway .count_maxConfirmations').html(response.maxConfirmation);
						
						jQuery('#nerva_gateway .count_paid').html(response.paid);
						jQuery('#nerva_gateway .count_amount').html(response.amount);
						jQuery('#nerva_gateway .diff').html((response.amount-response.paid).toFixed(12));
						
						jQuery('#nerva_gateway .meter #confirm-progress').css('width',''+Math.floor(response.currentConfirmations/response.maxConfirmation*100)+'%');
						jQuery('#nerva_gateway .meter #currency-progress').css('width',''+Math.floor(response.paid/response.amount*100)+'%');
						
						
					}
					console.log(response);
				}
			);
		});
	}
	
	update();
	var intervalRefreshStatus = setInterval(function(){
		update();
	}, <?= $this->reloadTime; ?>);
</script>

<div id="nerva_gateway" class="xnv-payment-container">
	<div class="header">
		<img src="<?= $pluginDirectory?>assets/png-nerva-logo-1024x1024.png" alt="Nerva" />
		<?php _e('Nerva Payment', $pluginIdentifier) ?>
	</div>
	<div class="content">
		<?php if($amount_xnv2===null): ?>
			<div class="status message important critical" id="nerva_gateway_error_generic">
				<?php _e('Your transaction cannot be processed currently. If you are the shop owner, please check your configuration', $pluginIdentifier) ?>
			</div>
		<?php endif; ?>
		
		<div id="nerva_gateway_payment_process" <?php if(!($displayedCurrentConfirmation !== null && $displayedCurrentConfirmation >= 0 && !$transactionConfirmed)): ?>style="display:none"<?php endif; ?>>
			<div class="status message important info">
				<i class="material-icons rotating" >replay</i>
				<?php _e('Your payment is being processed', $pluginIdentifier) ?> (<span class="count_currentConfirmations" ><?= $displayedCurrentConfirmation ?></span>/<span class="count_maxConfirmations" ><?= $displayedMaxConfirmation ?></span> <?php _e('confirmations', $pluginIdentifier) ?>)
				
			</div>
			<div class="meter">
				
				<span id="confirm-progress" class="progress" style="width: <?php echo $displayedCurrentConfirmation/$displayedMaxConfirmation*100; ?>%"></span>
				<span class="text" >(<span class="count_currentConfirmations" ><?= $displayedCurrentConfirmation ?></span>/<span class="count_maxConfirmations" ><?= $displayedMaxConfirmation ?></span>) <?php _e('confirmations', $pluginIdentifier) ?></span>
			
			
			</div>
			
			
			<div class="meter">
				
				<span id="currency-progress" class="progress" style="width: 0%"></span>
				<span class="text" >(<span class="count_paid" ><?= $paid ?></span>/<span class="count_amount" ><?= $amount ?></span>) Payment</span>

			</div>
			<span class="text" >Pending: <span class="diff"></span></span> 
		</div>
	
		<div id="nerva_gateway_payment_wait" <?php if(!(!$transactionConfirmed && $displayedCurrentConfirmation === null)): ?>style="display:none"<?php endif; ?>>
			<noscript>
				<div class="status message important critical">
					<?php _e('You must enable javascript in order to confirm your order', $pluginIdentifier) ?>
				</div>
			</noscript>
			<div class="status message important info">
				<i class="material-icons rotating" >replay</i>
				<?php _e('We are waiting for your transaction to be confirmed', $pluginIdentifier) ?>
			</div>
		
			<div class="message important" >
				<?php _e('Please send your XNV with those informations', $pluginIdentifier) ?>
			</div>
			<div class="xnv-amount-send">
				<div class="data-box" >
					<label><?php _e('Amount', $pluginIdentifier) ?></label>
					<input id="xnv_amount" type="text" disabled="disabled" class="value" value="">
					
					<button class="copy" onclick="setTextInClipboard('xnv_amount')" title="<?php _e('Copy', $pluginIdentifier) ?>"><i class="material-icons" >content_copy</i></button>
				</div>
				<div class="data-box" >
					<label><?php _e('Address', $pluginIdentifier) ?></label>
					<input id="xnv_address" disabled="disabled" type="text" class="value" value="<?= $displayedPaymentAddress ?>">
					<button class="copy" onclick="setTextInClipboard('xnv_address')" title="<?php _e('Copy', $pluginIdentifier) ?>"><i class="material-icons" >content_copy</i></button>
				</div>
				<?php if(isset($displayedPaymentId) && $displayedPaymentId !== null): ?>
				<div class="data-box" >
					<label><?php _e('Payment ID', $pluginIdentifier) ?></label>
					<input id="xnv_paymentId" type="text" disabled="disabled" class="value" value="<?= $displayedPaymentId ?>">
					<button class="copy" onclick="setTextInClipboard('xnv_paymentId')" title="<?php _e('Copy', $pluginIdentifier) ?>"><i class="material-icons" >content_copy</i></button>
				</div>
				<?php endif; ?>
			</div>
			<?php if(isset($qrUri)): ?>
				<div class="qr-code">
					<div class="message important"><?php _e('Or scan QR:', $pluginIdentifier) ?></div>
					<div><img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=<?= $qrUri ?>" /></div>
				</div>
			<?php endif; ?>
		</div>
	
		<div class="status message important success" id="nerva_gateway_payment_success" <?php if(!$transactionConfirmed): ?>style="display:none"<?php endif; ?> >
			<i class="material-icons" >check</i>
			<?php _e('Your transaction has been successfully confirmed!', $pluginIdentifier) ?>
		</div>
	</div>
	<div class="footer">
		<a href="https://getnerva.org" target="_blank"><?php _e('Help', $pluginIdentifier) ?></a> |
		<a href="https://getnerva.org" target="_blank"><?php _e('About Nerva', $pluginIdentifier) ?></a>
	</div>
</div>
