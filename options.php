<?php 

if (is_user_logged_in() && is_admin()) {
	
	load_plugin_textdomain($this->stringTextdomain, false, dirname(plugin_basename(__FILE__)) . '/translation');
	
	$adminSettings = $this->defaultOptions;

	if (isset($_POST['update-splg_options'])) {//save option changes
		foreach ($adminSettings as $key => $val){
			if (isset($_POST[$key])) {
				$adminSettings[$key] = trim($_POST[$key]);
			}
		}
	
		update_option('splg_options', $adminSettings);
	}
	
	$adminOptions = $this->getAdminOptions();
	
	$this->registerRewriteRules();
	$this->flushRewriteRules();
	
	$possibleProductTypes = $this->getTypesData(null);
	
	unset($_SESSION['_tac']);

?>
<style>
.form-table td {
	vertical-align: top;
}
ul.sploplist {
	list-style: inherit !important;
}
</style>
<div class="wrap">
  <?php 
  screen_icon(); 
  ?>
  <h2>Spreadplugin Plugin Options &raquo; Settings</h2>
  <div id="sprdplg-message" class="updated fade" style="display:none"></div>
  <div class="metabox-holder">
    <div class="meta-box-sortables ui-sortable">
      <div class="postbox">
        <div class="handlediv" title="Click to toggle"><br />
        </div>
        <h3 class="hndle">Spreadplugin
          <?php _e('Settings','spreadplugin'); ?>
        </h3>
        <div class="inside">
          <p>
            <?php _e('These settings will be used as default and can be overwritten by the extended shortcode.','spreadplugin'); ?>
          </p>
          <form action="options-general.php?page=splg_options&saved" method="post" id="splg_options_form" name="splg_options_form">
            <?php wp_nonce_field('splg_options'); ?>
            <table border="0" cellpadding="3" cellspacing="0" class="form-table">
              <tr>
                <td valign="top"><?php _e('Shop id:','spreadplugin'); ?></td>
                <td><input type="text" name="shop_id" value="<?php echo (empty($adminOptions['shop_id'])?0:$adminOptions['shop_id']); ?>" class="only-digit required" /></td>
              </tr>
              <tr>
                <td valign="top"><?php _e('Shop source:','spreadplugin'); ?></td>
                <td><select name="shop_source" id="shop_source" class="required">
                    <option value="net"<?php echo ($adminOptions['shop_source']=='net'?" selected":"") ?>>Europe</option>
                    <option value="com"<?php echo ($adminOptions['shop_source']=='com'?" selected":"") ?>>US/Canada/Australia/Brazil</option>
                  </select></td>
              </tr>
              <tr>
                <td valign="top"><?php _e('Spreadshirt API Key:','spreadplugin'); ?></td>
                <td><input type="text" name="shop_api" value="<?php echo $adminOptions['shop_api']; ?>" class="required" /></td>
              </tr>
              <tr>
                <td valign="top"><?php _e('Spreadshirt API Secret:','spreadplugin'); ?></td>
                <td><input type="text" name="shop_secret" value="<?php echo $adminOptions['shop_secret']; ?>" class="required" /></td>
              </tr>
              <tr>
                <td valign="top"><?php _e('Limit articles per page:','spreadplugin'); ?></td>
                <td><input type="text" name="shop_limit" value="<?php echo (empty($adminOptions['shop_limit'])?10:$adminOptions['shop_limit']); ?>" class="only-digit" /></td>
              </tr>
              <tr>
                <td valign="top"><?php _e('Image size:','spreadplugin'); ?></td>
                <td><select name="shop_imagesize" id="shop_imagesize">
                    <option value="190"<?php echo ($adminOptions['shop_imagesize']==190?" selected":"") ?>>190</option>
                    <option value="280"<?php echo ($adminOptions['shop_imagesize']==280?" selected":"") ?>>280</option>
                  </select>
                  px</td>
              </tr>
              <tr>
                <td valign="top"><?php _e('Product category:','spreadplugin'); ?></td>
                <td><select name="shop_productcategory" id="shop_productcategory">
                    <option value="">
                    <?php _e('All products','spreadplugin'); ?>
                    </option>
                    <?php 
					if (!empty($possibleProductTypes)) {
						foreach ($possibleProductTypes as $t => $v) {
							echo '<option value="' . $t . '"' . ($t == self::$shopOptions['shop_productcategory'] ? ' selected' : '') . '>' . $t . '</option>';
						}
					}
					?>
                  </select></td>
              </tr>
              <tr>
                <td valign="top"><?php _e('Article category:','spreadplugin'); ?></td>
                <td>Please see <strong>How do I get the category Id?</strong> in FAQ<br />
                  <br />
                  <input type="text" name="shop_category" value="<?php echo $adminOptions['shop_category']; ?>" class="only-digit" /></td>
              </tr>
              <tr>
                <td valign="top"><?php _e('Social buttons:','spreadplugin'); ?></td>
                <td><input type="radio" name="shop_social" value="0"<?php echo ($adminOptions['shop_social']==0?" checked":"") ?> />
                  <?php _e('Disabled','spreadplugin'); ?>
                  <br />
                  <input type="radio" name="shop_social" value="1"<?php echo ($adminOptions['shop_social']==1?" checked":"") ?> />
                  <?php _e('Enabled','spreadplugin'); ?></td>
              </tr>
              <tr>
                <td valign="top"><?php _e('Product linking:','spreadplugin'); ?></td>
                <td><input type="radio" name="shop_enablelink" value="0"<?php echo ($adminOptions['shop_enablelink']==0?" checked":"") ?> />
                  <?php _e('Disabled','spreadplugin'); ?>
                  <br />
                  <input type="radio" name="shop_enablelink" value="1"<?php echo ($adminOptions['shop_enablelink']==1?" checked":"") ?> />
                  <?php _e('Enabled','spreadplugin'); ?></td>
              </tr>
              <tr>
                <td valign="top"><?php _e('Sort articles by:','spreadplugin'); ?></td>
                <td><select name="shop_sortby" id="shop_sortby">
                    <option>place</option>
                    <?php if (!empty(self::$shopArticleSortOptions)) {
		  foreach (self::$shopArticleSortOptions as $val) {
			  ?>
                    <option value="<?php echo $val; ?>"<?php echo ($adminOptions['shop_sortby']==$val?" selected":"") ?>><?php echo $val; ?></option>
                    <?php }
	  }
	  ?>
                  </select></td>
              </tr>
              <tr>
                <td valign="top"><?php _e('Target of checkout links:','spreadplugin'); ?></td>
                <td><?php _e('Enter the name of your target iframe or frame, if available. Default is _blank (new window).','spreadplugin'); ?>
                  <br />
                  <br />
                  <input type="text" name="shop_linktarget" value="<?php echo (empty($adminOptions['shop_linktarget'])?'_self':$adminOptions['shop_linktarget']); ?>" /></td>
              </tr>
              <tr>
                <td valign="top"><?php _e('Use iframe for checkout:','spreadplugin'); ?></td>
                <td><input type="radio" name="shop_checkoutiframe" value="0"<?php echo ($adminOptions['shop_checkoutiframe']==0?" checked":"") ?> />
                  <?php _e('Opens in separate window','spreadplugin'); ?>
                  <br />
                  <input type="radio" name="shop_checkoutiframe" value="1"<?php echo ($adminOptions['shop_checkoutiframe']==1?" checked":"") ?> />
                  <?php _e('Opens an iframe in the page content','spreadplugin'); ?>
                  <br />
                  <input type="radio" name="shop_checkoutiframe" value="2"<?php echo ($adminOptions['shop_checkoutiframe']==2?" checked":"") ?> />
                  <?php _e('Opens an iframe in a modal window','spreadplugin'); ?></td>
              </tr>
              <tr>
                <td valign="top"><?php _e('Use designer:','spreadplugin'); ?></td>
                <td><input type="radio" name="shop_designer" value="0"<?php echo ($adminOptions['shop_designer']==0?" checked":"") ?> />
                  <?php _e('None','spreadplugin'); ?>
                  <br />
                  <input type="radio" name="shop_designer" value="1"<?php echo ($adminOptions['shop_designer']==1||$adminOptions['shop_designer']==2?" checked":"") ?> />
                  <?php _e('Designer (Spreadshirt Tablomat)','spreadplugin'); ?>
                  <br />
                  <br />
                  <?php _e('Designer Shop Id','spreadplugin'); ?>
                  <input type="text" name="shop_designershop" value="<?php echo $adminOptions['shop_designershop']; ?>" class="only-digit" />
                  <br />
                  <?php _e('If you have a Designer Shop at Spreadshirt then enter its ID here to only show the designs of your Designer Shop, otherwise all Spreadshirt Marketplace designs are shown.','spreadplugin'); ?></td>
              </tr>
              <tr>
                <td valign="top"><?php _e('Default display:','spreadplugin'); ?></td>
                <td><input type="radio" name="shop_display" value="0"<?php echo ($adminOptions['shop_display']==0?" checked":"") ?> />
                  <?php _e('Articles','spreadplugin'); ?>
                  <br />
                  <input type="radio" name="shop_display" value="1"<?php echo ($adminOptions['shop_display']==1?" checked":"") ?> />
                  <?php _e('Designs','spreadplugin'); ?></td>
              </tr>
              <tr>
                <td valign="top"><?php _e('Designs with background:','spreadplugin'); ?></td>
                <td><?php _e('Displays designs with background color of each first given article/shirt','spreadplugin'); ?>
                  <br />
                  <br />
                  <input type="radio" name="shop_designsbackground" value="0"<?php echo ($adminOptions['shop_designsbackground']==0?" checked":"") ?> />
                  <?php _e('Disabled','spreadplugin'); ?>
                  <br />
                  <input type="radio" name="shop_designsbackground" value="1"<?php echo ($adminOptions['shop_designsbackground']==1?" checked":"") ?> />
                  <?php _e('Enabled','spreadplugin'); ?></td>
              </tr>
              <tr>
                <td valign="top"><?php _e('Always show article description:','spreadplugin'); ?></td>
                <td><input type="radio" name="shop_showdescription" value="0"<?php echo ($adminOptions['shop_showdescription']==0?" checked":"") ?> />
                  <?php _e('Disabled','spreadplugin'); ?>
                  <br />
                  <input type="radio" name="shop_showdescription" value="1"<?php echo ($adminOptions['shop_showdescription']==1?" checked":"") ?> />
                  <?php _e('Enabled','spreadplugin'); ?></td>
              </tr>
              <tr>
                <td valign="top"><?php _e('Show product description under article description:','spreadplugin'); ?></td>
                <td><input type="radio" name="shop_showproductdescription" value="0"<?php echo ($adminOptions['shop_showproductdescription']==0?" checked":"") ?> />
                  <?php _e('Disabled','spreadplugin'); ?>
                  <br />
                  <input type="radio" name="shop_showproductdescription" value="1"<?php echo ($adminOptions['shop_showproductdescription']==1?" checked":"") ?> />
                  <?php _e('Enabled','spreadplugin'); ?></td>
              </tr>
              <tr>
                <td valign="top"><?php _e('Display price without and with tax:','spreadplugin'); ?></td>
                <td><input type="radio" name="shop_showextendprice" value="0"<?php echo ($adminOptions['shop_showextendprice']==0?" checked":"") ?> />
                  <?php _e('Disabled','spreadplugin'); ?>
                  <br />
                  <input type="radio" name="shop_showextendprice" value="1"<?php echo ($adminOptions['shop_showextendprice']==1?" checked":"") ?> />
                  <?php _e('Enabled','spreadplugin'); ?></td>
              </tr>
              <tr>
                <td valign="top"><?php _e('Zoom image background color:','spreadplugin'); ?></td>
                <td><input type="text" name="shop_zoomimagebackground" class="colorpicker" value="<?php echo (empty($adminOptions['shop_zoomimagebackground'])?'#FFFFFF':$adminOptions['shop_zoomimagebackground']); ?>" data-default-color="#FFFFFF" maxlength="7" /></td>
              </tr>
              <tr>
                <td valign="top"><?php _e('View:','spreadplugin'); ?></td>
                <td><input type="radio" name="shop_view" value="0"<?php echo ($adminOptions['shop_view']==0 || $adminOptions['shop_view']==''?" checked":"") ?> />
                  <?php _e('Grid view','spreadplugin'); ?>
                  <br />
                  <input type="radio" name="shop_view" value="1"<?php echo ($adminOptions['shop_view']==1?" checked":"") ?> />
                  <?php _e('List view','spreadplugin'); ?>
                  <br />
                  <input type="radio" name="shop_view" value="2"<?php echo ($adminOptions['shop_view']==2?" checked":"") ?> />
                  <?php _e('Min view','spreadplugin'); ?>
                  (Disables Zoom, too)</td>
              </tr>
              <tr>
                <td valign="top"><?php _e('Basket text or icon:','spreadplugin'); ?></td>
                <td><input type="radio" name="shop_basket_text_icon" value="0"<?php echo ($adminOptions['shop_basket_text_icon']==0 || $adminOptions['shop_basket_text_icon']==''?" checked":"") ?> />
                  <?php _e('Text','spreadplugin'); ?>
                  <br />
                  <input type="radio" name="shop_basket_text_icon" value="1"<?php echo ($adminOptions['shop_basket_text_icon']==1?" checked":"") ?> />
                  <?php _e('Icon','spreadplugin'); ?></td>
              </tr>
              <tr>
                <td valign="top"><?php _e('Infinity scrolling:','spreadplugin'); ?></td>
                <td><input type="radio" name="shop_infinitescroll" value="0"<?php echo ($adminOptions['shop_infinitescroll']==0?" checked":"") ?> />
                  <?php _e('Disabled','spreadplugin'); ?>
                  <br />
                  <input type="radio" name="shop_infinitescroll" value="1"<?php echo ($adminOptions['shop_infinitescroll']==1 || $adminOptions['shop_infinitescroll']==''?" checked":"") ?> />
                  <?php _e('Enabled','spreadplugin'); ?></td>
              </tr>
              <tr>
                <td valign="top"><?php _e('Lazy load:','spreadplugin'); ?></td>
                <td><input type="radio" name="shop_lazyload" value="0"<?php echo ($adminOptions['shop_lazyload']==0?" checked":"") ?> />
                  <?php _e('Disabled','spreadplugin'); ?>
                  <br />
                  <input type="radio" name="shop_lazyload" value="1"<?php echo ($adminOptions['shop_lazyload']==1 || $adminOptions['shop_lazyload']==''?" checked":"") ?> />
                  <?php _e('Enabled','spreadplugin'); ?>
                  <br />
                  <br />
                  <?php _e('If active, load images on view (speed up page load).','spreadplugin'); ?></td>
              </tr>
              <tr>
                <td valign="top"><?php _e('Zoom type:','spreadplugin'); ?></td>
                <td><input type="radio" name="shop_zoomtype" value="0"<?php echo ($adminOptions['shop_zoomtype']==0 || $adminOptions['shop_zoomtype']==''?" checked":"") ?> />
                  <?php _e('Inner','spreadplugin'); ?>
                  <br />
                  <input type="radio" name="shop_zoomtype" value="1"<?php echo ($adminOptions['shop_zoomtype']==1?" checked":"") ?> />
                  <?php _e('Lens','spreadplugin'); ?>
                  <br />
                  <input type="radio" name="shop_zoomtype" value="2"<?php echo ($adminOptions['shop_zoomtype']==2?" checked":"") ?> />
                  <?php _e('Disabled','spreadplugin'); ?></td>
              </tr>
              <tr>
                <td valign="top"><?php _e('Shop language:','spreadplugin'); ?></td>
                <td><select name="shop_language" id="shop_language">
                    <option value=""<?php echo (empty($adminOptions['shop_language'])?" selected":"") ?>>
                    <?php _e('Wordpress installation language (default)','spreadplugin'); ?>
                    </option>
                    <option value="da_DK"<?php echo ($adminOptions['shop_language']=='da_DK'?" selected":"") ?>>Dansk</option>
                    <option value="de_DE"<?php echo ($adminOptions['shop_language']=='de_DE'?" selected":"") ?>>Deutsch</option>
                    <option value="nl_NL"<?php echo ($adminOptions['shop_language']=='nl_NL'?" selected":"") ?>>Dutch (Nederlands)</option>
                    <option value="fi_FI"<?php echo ($adminOptions['shop_language']=='fi_FI'?" selected":"") ?>>Suomi</option>
                    <option value="es_ES"<?php echo ($adminOptions['shop_language']=='es_ES'?" selected":"") ?>>Español</option>
                    <option value="fr_FR"<?php echo ($adminOptions['shop_language']=='fr_FR'?" selected":"") ?>>French</option>
                    <option value="it_IT"<?php echo ($adminOptions['shop_language']=='it_IT'?" selected":"") ?>>Italiano</option>
                    <option value="nb_NO"<?php echo ($adminOptions['shop_language']=='nb_NO'?" selected":"") ?>>Norsk</option>
                    <option value="nn_NO"<?php echo ($adminOptions['shop_language']=='nn_NO'?" selected":"") ?>>Nynorsk</option>
                    <option value="pl_PL"<?php echo ($adminOptions['shop_language']=='pl_PL'?" selected":"") ?>>Jezyk polski</option>
                    <option value="pt_PT"<?php echo ($adminOptions['shop_language']=='pt_PT'?" selected":"") ?>>Português</option>
                    <option value="jp_JP"<?php echo ($adminOptions['shop_language']=='jp_JP'?" selected":"") ?>>Japanese</option>
                    <option value="be_FR"<?php echo ($adminOptions['shop_language']=='be_FR'?" selected":"") ?>>Belgium / French</option>
                    <option value="sv_SE"<?php echo ($adminOptions['shop_language']=='sv_SE'?" selected":"") ?>>Swedish</option>
                    <option value="en_GB"<?php echo ($adminOptions['shop_language']=='en_GB'?" selected":"") ?>>English (GB)</option>
                    <option value="us_US"<?php echo ($adminOptions['shop_language']=='us_US'?" selected":"") ?>>English (US)</option>
                  </select></td>
              </tr>
              <tr>
                <td valign="top"><?php _e('Anchor:','spreadplugin'); ?></td>
                <td>#
                  <input type="text" name="shop_url_anchor" placeholder="<?php _e('splshop or similar','spreadplugin'); ?>" value="<?php echo (empty($adminOptions['shop_url_anchor'])?"":$adminOptions['shop_url_anchor']); ?>" />
                  <br />
                  <?php _e('If you are using one page themes or want to specify an anchor to add with url, enter it here. Please avoid using the same anchor name as in your menu - some themes are blocking it.','spreadplugin'); ?></td>
              </tr>
              <tr>
                <td valign="top"><?php _e('Product detail slug:','spreadplugin'); ?></td>
                <td><input type="text" name="shop_url_productdetail_slug" placeholder="<?php _e('splproduct or similar','spreadplugin'); ?>" value="<?php echo (empty($adminOptions['shop_url_productdetail_slug'])?"splproduct":$adminOptions['shop_url_productdetail_slug']); ?>" class="only-letters" />
                  <br />
                  <?php _e('Don\'t change if unknown! You could harm your site - dangerous.<br>Anyway, you could change the product detail link name here (SEO, Permalink).','spreadplugin'); ?></td>
              </tr>
              <tr>
                <td valign="top"><?php _e('Custom CSS'); ?></td>
                <td><textarea style="width: 300px; height: 215px; background: #EEE;" name="shop_customcss" class="custom-css"><?php echo stripslashes(htmlspecialchars($adminOptions['shop_customcss'], ENT_QUOTES)); ?></textarea></td>
              </tr>
              <tr>
                <td valign="top"><?php _e('Debug mode:','spreadplugin'); ?></td>
                <td><input type="radio" name="shop_debug" value="0"<?php echo ($adminOptions['shop_debug']==0 || $adminOptions['shop_debug']==''?" checked":"") ?> />
                  <?php _e('Off','spreadplugin'); ?>
                  <br />
                  <input type="radio" name="shop_debug" value="1"<?php echo ($adminOptions['shop_debug']==1?" checked":"") ?> />
                  <?php _e('On','spreadplugin'); ?>
                  <br />
                  If active, all your spreadshirt/spreadplugin data could be exposed, so please be carefull with this option!</td>
              </tr>
              <tr>
                <td valign="top"><?php _e('Sleep timer:','spreadplugin'); ?></td>
                <td><input type="text" name="shop_sleep" value="<?php echo (empty($adminOptions['shop_sleep'])?0:intval($adminOptions['shop_sleep'])); ?>" class="only-digit" />
                  <br />
                  <strong>Don't change this value, if you have no problems rebuilding your article cache, otherwise it would take very long!</strong> Changing this value is only neccessary if you are experiencing problems when rebuilding cache. Some webspaces (e.g. godaddy.com) have request limits, which you can avoid by setting this value to for example 10.</td>
              </tr>
              <tr>
                <td valign="top"><?php _e('Read quantity of articles (max):','spreadplugin'); ?></td>
                <td><select name="shop_max_quantity_articles" id="shop_max_quantity_articles">
                    <option value="1"<?php echo ($adminOptions['shop_max_quantity_articles']==1?" selected":"") ?>>1</option>
                    <option value="5"<?php echo ($adminOptions['shop_max_quantity_articles']==5?" selected":"") ?>>5</option>
                    <option value="10"<?php echo ($adminOptions['shop_max_quantity_articles']==10?" selected":"") ?>>10</option>
                    <option value="100"<?php echo ($adminOptions['shop_max_quantity_articles']==100?" selected":"") ?>>100</option>
                    <option value="200"<?php echo ($adminOptions['shop_max_quantity_articles']==200?" selected":"") ?>>200</option>
                    <option value="1000"<?php echo (empty($adminOptions['shop_max_quantity_articles']) || $adminOptions['shop_max_quantity_articles']==1000?" selected":"") ?>>All (default)</option>
                  </select>
                  <br />
                  <?php _e('Limit the quantity of articles which will be read. Use a lower value if you have problems saving the articles.','spreadplugin'); ?></td>
              </tr>
              <tr>
                <td valign="top"><?php _e('Red Sky Theme one page (Custom Part) Workaround:','spreadplugin'); ?></td>
                <td><input type="radio" name="shop_rscuwo" value="0"<?php echo ($adminOptions['shop_rscuwo']==0 || $adminOptions['shop_rscuwo']==''?" checked":"") ?> />
                  <?php _e('Off','spreadplugin'); ?>
                  <br />
                  <input type="radio" name="shop_rscuwo" value="1"<?php echo ($adminOptions['shop_rscuwo']==1?" checked":"") ?> />
                  <?php _e('On','spreadplugin'); ?></td>
              </tr>
              <tr>
                <td valign="top"><?php _e('Change "Back to shop" link in checkout:','spreadplugin'); ?></td>
                <td><input type="text" name="shop_backtoshopurl" style="min-width:300px" placeholder="<?php _e('http://www.example.com or empty if default','spreadplugin'); ?>" value="<?php echo (empty($adminOptions['shop_backtoshopurl'])?"":$adminOptions['shop_backtoshopurl']); ?>" /></td>
              </tr>
              <tr>
                <td valign="top"><?php _e('Product on stock check:','spreadplugin'); ?></td>
                <td><input type="radio" name="shop_stockstates" value="0"<?php echo ($adminOptions['shop_stockstates']==0 || $adminOptions['shop_rscuwo']==''?" checked":"") ?> />
                  <?php _e('Off','spreadplugin'); ?>
                  <br />
                  <input type="radio" name="shop_stockstates" value="1"<?php echo ($adminOptions['shop_stockstates']==1?" checked":"") ?> />
                  <?php _e('On','spreadplugin'); ?></td>
              </tr>
            </table>
            <input type="submit" name="update-splg_options" id="update-splg_options" class="button-primary" value="<?php _e('Update settings','spreadplugin'); ?>" />
            <input type="button" onclick="javascript:rebuild();" class="button-primary" value="<?php _e('Rebuild cache','spreadplugin'); ?>" />
          </form>
        </div>
      </div>
      <div class="postbox">
        <div class="handlediv" title="Click to toggle"><br />
        </div>
        <h3 class="hndle">Shortcode Samples</h3>
        <div class="inside">
          <h4>
            <?php _e('Minimum required shortcode','spreadplugin'); ?>
          </h4>
          <p>[spreadplugin]</p>
          <h4>
            <?php _e('Sample shortcode with custom category','spreadplugin'); ?>
          </h4>
          <p>[spreadplugin shop_category=&quot;CATEGORYID&quot;]</p>
          <p>&nbsp;</p>
          <h4>Possible values and shortcodes for pre-defined (Spreadshirt default) categories and their sub-categories are:</h4>
          <p>
          <ul class="sploplist">
            <?php

if (!empty($possibleProductTypes)) {
	foreach($possibleProductTypes as $t => $v) {
		
		echo '<li><strong>'.$t.':</strong><br>[spreadplugin shop_productcategory="'.$t.'"]</li><ul>';
		
		if (!empty($v)) {
			foreach ($v as $st => $sv) {
				if ($st!='all')	echo '<li>- <strong>'. $st . ':</strong><br>[spreadplugin shop_productcategory="'.$t.'" shop_productsubcategory="'.$st.'"]</li>';
			}
		}
		
		echo "</ul>";
	}
}
?>
          </ul>
          </p>
          <p>&nbsp;</p>
          <h4>
            <?php _e('Use one of the following shortcode extensions to overwrite or extend each single page.','spreadplugin'); ?>
            (only for experienced users) </h4>
          <p>
            <?php
  
  $_plgop = '';
  foreach ($adminOptions as $k => $v) {
	  if (!in_array($k,array('shop_infinitescroll','shop_customcss','shop_debug','shop_sleep','shop_url_productdetail_slug'))) {	
		$_plgop .= $k.'="'.$v.'"<br>';
	  }
  }
  
  echo trim($_plgop);
  
  ?>
          </p>
          <p>&nbsp;</p>
        </div>
      </div>
    </div>
  </div>
  <p>If you experience any problems or have suggestions, feel free to leave a message on <a href="http://wordpress.org/support/plugin/wp-spreadplugin" target="_blank">wordpress</a>.<br />
  </p>
  <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=EZLKTKW8UR6PQ" target="_blank"><img src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" alt="Jetzt einfach, schnell und sicher online bezahlen mit PayPal." /></a>
  <p>All donations valued greatly</p>
</div>
<script language='javascript' type='text/javascript'>
function setMessage(msg) {
	jQuery("#sprdplg-message").append(msg); //.html(msg)
	jQuery("#sprdplg-message").show();
}

function rebuildItem(listcontent,cur1,cur2) {
	
	if (cur2==0) {
		setMessage((typeof listcontent[cur1].title !== 'undefined'?"<h3>" + listcontent[cur1].title + "</h3>":"") + "<p>Rebuilding Page " + (cur1+1) + " of " + listcontent.length + "...</p>");
	}
	
	
	if (cur2 >= listcontent[cur1].items.length) {
		setMessage("Done<br>");
		
		// storing items
		jQuery.ajax({
			url: "<?php echo admin_url('admin-ajax.php'); ?>",
			type: "POST",
			data: "action=rebuildCache&do=save&_pageid=" + listcontent[cur1].id + "&_ts=" + (new Date()).getTime(),
			timeout: 360000,
			cache: false,
			success: function(result) {
				//console.debug(result);
				setMessage("<p>Successfully stored page " + cur1 + "</p>");
			},
			error: function(request, status, error) {
				setMessage("<p>Error " + request.status + " storing page " + cur1 + "</p>");
			}
			
		});
		
		// next page
		cur1 = cur1 + 1;
		
		if (listcontent[cur1]) {
			rebuildItem(listcontent,cur1,0);
		}

		return;
	}
	
	
	setMessage("Rebuilding Item " + (cur2+1) + " of " + listcontent[cur1].items.length + " (" + listcontent[cur1].items[cur2].articlename + ") <img src='" + listcontent[cur1].items[cur2].previewimage + "' width='32' height='32'>... ");

	jQuery.ajax({
		url: "<?php echo admin_url('admin-ajax.php'); ?>",
		type: "POST",
		data: "action=rebuildCache&do=rebuild&_pageid=" + listcontent[cur1].id + "&_articleid=" + listcontent[cur1].items[cur2].articleid + "&_pos=" + listcontent[cur1].items[cur2].place + "&_ts=" + (new Date()).getTime(),
		success: function(result) {
			setMessage(result + ' <br>');
			
			// next item
			cur2 = cur2 + 1;
			rebuildItem(listcontent,cur1,cur2);
		},
		error: function(request, status, error) {
			setMessage("Request not performed error " + request.status + '. Try next<br>');
			
			// skip to next item
			cur2 = cur2 + 1;
			rebuildItem(listcontent,cur1,cur2);
		}
		
	});
}
				
function rebuild() {
		
	jQuery('html, body').animate({scrollTop: 0}, 800);
	setMessage("<p>Reading pages. Please wait...</p>");
	
	jQuery.ajax({
		url: "<?php echo admin_url('admin-ajax.php'); ?>",
		type: "POST",
		data: "action=rebuildCache&do=getlist" + "&_ts=" + (new Date()).getTime(),
		timeout: 360000,
		cache: false,
		dataType: 'json',
		success: function(result) {
			var list = result;

			if (!list) {
				setMessage("<p>No pages found.</p>");
				return;
			}
	
			var curr1 = 0;				
			var curr2 = 0;

			rebuildItem(list,curr1,curr2);
		},
		error: function(request, status, error) {
			setMessage("Getlist not performed error '" + error + " (" + request.status + ")'. Please check the browser console for more informations." + '<br>');
			console.log("Got following error message: " + request.responseText);
		}
	});
}
			

jQuery('.only-digit').keyup(function() {
	if (/\D/g.test(this.value)) {
		// Filter non-digits from input value.
		this.value = this.value.replace(/\D/g, '');
	}
});
jQuery('.only-letters').keyup(function() {
	if (/[^a-z]/gi.test(this.value)) {
		// Filter non-letters from input value.
		this.value = this.value.replace(/[^a-z]/gi, '');
	}
});

// select different locale if north america is set
jQuery('#shop_locale').change(function() {
	var sel = jQuery(this).val();

	if (sel == 'us_US' || sel == 'us_CA' || sel == 'fr_CA') {
		jQuery('#shop_source').val('com');
	} else {
		jQuery('#shop_source').val('net');
	}
});

// bind to the form's submit event
jQuery('#splg_options_form').submit(function() {

	var isFormValid = true;
		
	jQuery("#splg_options_form .required").each(function() { 
		if (jQuery.trim(jQuery(this).val()).length == 0) {
			jQuery(this).parent().addClass("highlight");
			isFormValid = false;
		} else {
			jQuery(this).parent().removeClass("highlight");
		}
	});
	
	
	// Formularprüfung
	if (!isFormValid) { 	
		setMessage("<p><?php _e('Please fill in the highlighted fields!','spreadplugin'); ?></p>");
	} else {
		return true;
	}

	return false;
});

// add color picker
jQuery(document).ready(function() {  
	jQuery('.colorpicker').wpColorPicker();  
});
</script>
<?php 
if (isset($_GET['saved'])) {
	/*echo '<script language="javascript">rebuild();</script>';*/
	echo '<script language="javascript">setMessage("<p>'.__('Successfully saved settings. Please click rebuild cache if necessary.','spreadplugin').'</p>");</script>';
}


} ?>
