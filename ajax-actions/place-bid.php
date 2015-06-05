<script type="text/javascript">
jQuery(document).ready(function($){
    var ajaxurl = '<?php echo admin_url('admin-ajax.php');?>';
    var bid_name='<?php echo esc_js($auction_bidder_name);?>';
    var bid_email='<?php echo $auction_bidder_email;?>';
    var epattern = new RegExp(/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i);
    
    $("#wdm-place-bid-now").click(function(){
	
        $.blockUI({ message: null }); 

	var bid_val = new Number;
	bid_val = $("#wdm-bidder-bidval").val();
	if (bid_name=='') {
	   bid_name=$('#wdm-bidder-name').val();
	    if (bid_name=='') {
		$.unblockUI();
		alert('<?php _e("Please enter your name", "wdm-ultimate-auction");?>');
	    }
	}
	if (bid_email=='') {
	   bid_email=$('#wdm-bidder-email').val();
	   if (bid_email=='') {
	    $.unblockUI();
	    alert('<?php _e("Please enter your email", "wdm-ultimate-auction");?>');
	   }
	   else
	   {
		if (!epattern.test(bid_email)) {
		    $.unblockUI();
		    alert('<?php _e("Please enter valid email id", "wdm-ultimate-auction");?>');
		}
	   }
	}
	if(!bid_val)
	{
	    $.unblockUI();
	    alert('<?php _e("Please enter your Bid Amount", "wdm-ultimate-auction");?>');
	}
	else if( bid_val && isNaN(bid_val))
	{
	    $.unblockUI();
	    alert('<?php _e("Please enter a numeric value", "wdm-ultimate-auction");?>');
	}
	else
	{
	    
	     var data = {
		action: 'place_bid_now',
		ab_name: bid_name,
                ab_email: bid_email,
                ab_bid: $("#wdm-bidder-bidval").val(),
                auction_id: "<?php echo $wdm_auction->ID; ?>",
		auc_name: "<?php echo esc_js($wdm_auction->post_title); ?>",
		auc_desc: "<?php echo esc_js($wdm_auction->post_content); ?>",
		auc_url: "<?php echo get_permalink();?>",
		ab_char: "<?php echo $set_char;?>"
	    };
	    $.post(ajaxurl, data, function(rs) {
		
		var latest_bid;
		var curr_next_bid = new Number;
		curr_next_bid = "<?php echo $inc_price; ?>";
		
		var response = JSON.parse(rs);
		if(response.stat == "inv_bid")
		{
		    latest_bid = response.bid;
		    
		    $.unblockUI();
		    
		    if(Number(bid_val) >= Number(curr_next_bid))
			alert('<?php printf(__("Sorry, an another bidder has bid on the previous bid amount. Please enter a bid amount greater than or equal to %s", "wdm-ultimate-auction"), "" );?> ' + latest_bid);
		    else
		    {
			alert('<?php printf(__("Please enter a bid amount greater than or equal to %s", "wdm-ultimate-auction"), "" );?>' + latest_bid);
			return false;
		    }
		    window.location.reload();
		}
		else if(response.stat == "Expired")
		{
		    $.unblockUI();
		    alert('<?php _e("Sorry, this auction has been expired.", "wdm-ultimate-auction");?>');
		    window.location.reload();
		}
		else if(response.stat == "Sold")
		{
		    $.unblockUI();
		    alert('<?php _e("Sorry, your bid can not be placed. It seems that either a bidder has outbid you or the auction has been expired recently.", "wdm-ultimate-auction");?>');
		    window.location.reload();
		}
		else if(response.stat == "Won")
		{
		    var wdmmsg = '';
		    var mod_bid = new Number;
		    mod_bid = response.bid;
		    mod_bid = Number(mod_bid);
		    
		    if (response.type == 'simple') {
			
		    wdmmsg = "<?php echo str_replace('"', "'", __("Congratulations! You have won this auction since your bid value has reached the 'Buy it Now' price.", "wdm-ultimate-auction"));?>";
		    
		    var w_data = {
				    action: 'bid_notification',
				    email_type: 'winner_email',
				    ab_name: bid_name,
				    ab_email: bid_email,
				    ab_bid: $("#wdm-bidder-bidval").val(),
				    md_bid: mod_bid,
				    auction_id: "<?php echo $wdm_auction->ID; ?>",
				    auc_name: "<?php echo esc_js($wdm_auction->post_title); ?>",
				    auc_desc: "<?php echo esc_js($wdm_auction->post_content); ?>",
				    auc_url: "<?php echo get_permalink();?>",
				    ab_char: "<?php echo $set_char;?>"
			};
		    }
		    else{
			wdmmsg = "<?php echo str_replace('"', "'", __("Sorry, you have been outbid by the current highest bidder for 'Buy it now' price.", "wdm-ultimate-auction"));?>";
			
			var w_data = {
				    action: 'other_bid_notification',
				    adm_email: response.adm_email,
				    mod_bid: response.mod_bid,
				    auc_name: response.auc_name,
				    auc_desc: response.auc_desc,
				    mod_email: response.mod_email,
				    mod_name: response.mod_name,
				    orig_email: response.orig_email,
				    orig_bid: response.orig_bid,
				    ret_url: response.ret_url,
				    auc_id: response.auc_id,
				    stat: response.stat
			};
		    }
			$.post(ajaxurl, w_data, function(resp) {$.unblockUI();
			    alert('<?php _e("Your Bid Placed Successfully!", "wdm-ultimate-auction");?>');
			    if(wdmmsg != '') alert(wdmmsg);
			window.location.reload();});
		}
		else if(response.stat == "Placed")
		{
		   var wdmmsg = '';
		    var mod_bid = new Number;
		    mod_bid = response.bid;
		    mod_bid = Number(mod_bid);
		    
		    if (response.type == 'simple') {
		    
		    if (Number('<?php echo $to_buy;?>') > 0 && Number($("#wdm-bidder-bidval").val()) >= Number('<?php echo $to_buy;?>')) {
			wdmmsg = "<?php echo str_replace('"', "'", __("You can be winner if your bid reaches 'Buy it now' price by automatic bidding.", "wdm-ultimate-auction"));?>";
		    }
		    
		    var b_data = {
				    action: 'bid_notification',
				    ab_name: bid_name,
				    ab_email: bid_email,
				    ab_bid: $("#wdm-bidder-bidval").val(),
				    md_bid: mod_bid,
				    auction_id: "<?php echo $wdm_auction->ID; ?>",
				    auc_name: "<?php echo esc_js($wdm_auction->post_title); ?>",
				    auc_desc: "<?php echo esc_js($wdm_auction->post_content); ?>",
				    auc_url: "<?php echo get_permalink();?>",
				    ab_char: "<?php echo $set_char;?>"
			};
		    }
		    else{
			wdmmsg = "<?php _e("You have been outbid by the current highest bidder.", "wdm-ultimate-auction");?>";
			
			var b_data = {
				    action: 'other_bid_notification',
				    adm_email: response.adm_email,
				    mod_bid: response.mod_bid,
				    auc_name: response.auc_name,
				    auc_desc: response.auc_desc,
				    mod_email: response.mod_email,
				    mod_name: response.mod_name,
				    orig_email: response.orig_email,
				    orig_bid: response.orig_bid,
				    ret_url: response.ret_url,
				    auc_id: response.auc_id,
				    stat: response.stat
			};
		    }
			$.post(ajaxurl, b_data, function(r) {
			    $.unblockUI();
			    alert('<?php _e("Your Bid Placed Successfully!", "wdm-ultimate-auction");?>');
			    if(wdmmsg != '') alert(wdmmsg);   
			    window.location.reload();});
		}
		else if(response.stat == "email_exists")
		{
		    $.unblockUI();
		   alert('<?php _e("Sorry, this email is already registered for a user.", "wdm-ultimate-auction");?>');
		   window.location.reload();
		}
		else
		{
		    $.unblockUI();
		   alert('<?php _e("Sorry, your bid can not be placed.", "wdm-ultimate-auction");?>');
		   window.location.reload();
		}
		
                $("#wdm-bidder-bidval").val("");
		
	    });
	}
	
        return false;
        });
    
    });
</script>