<?php
/*
Plugin Name: MYC4 importer 
Plugin URI: http://microfinance.fm/myc4-importer
Description: Allows you to import your bids on microfinance site <a href="http://myc4.com">MYC4</a>  into your blog as posts. Plugin idea by <a href="http://david.fm">prof. David Costa</a> of <a href="http://college.ch">Robert Kennedy College</a>. 
Version: 0.1
License: GPL
Author: Mihai Secasiu
Author URI: http://patchlog.com
*/

function myc4_get_image($dir,$img)
{
	
	@mkdir($dir.dirname($img),0750,true);
	if(!is_file($dir.$img)){
		if($fc=file_get_contents('http://www.myc4.com'.$img)){
			if(file_put_contents($dir.$img,$fc))return true;
			else echo "error writing file: $dir.$img";
		}
	}
	return false;
}


function myc4_bdate($str)
{
	// 22.03.2009 03:44 CET
	if($tsa=strptime($str,'%d.%m.%Y %H:%M %Z')){
		$unx = mktime(
                    $tsa['tm_hour'],
                    $tsa['tm_min'],
                    $tsa['tm_sec'],
                    1 ,
                    $tsa['tm_yday'] + 1,
                   $tsa['tm_year'] + 1900
                 );
		return strftime('%Y-%m-%d %H:%M:%S',$unx); 
	}

}

function myc4_eval($eval_str,$b)
{
        preg_match_all("/(<\?php|<\?)(.*?)\?>/si", $eval_str,$raw_php_matches);
        $php_idx = 0;
        while (isset($raw_php_matches[0][$php_idx]))
        {
                $raw_php_str = $raw_php_matches[0][$php_idx];
                $raw_php_str = str_replace("<?php", "", $raw_php_str);
                $raw_php_str = str_replace("<?", "", $raw_php_str);
                $raw_php_str = str_replace("?>", "", $raw_php_str);

		if($raw_php_str[0]=='='){
			$raw_php_str=preg_replace('/^=(.*)/','echo $1;',$raw_php_str);
		}
                ob_start();
                if(eval("$raw_php_str")===FALSE){
			echo "error evaluating: $raw_php_str";
			ob_end_flush();
			die();
		}
                $exec_php_str = ob_get_contents();
                ob_end_clean();
                $eval_str = preg_replace("/(<\?php|<\?)(.*?)\?>/si",$exec_php_str, $eval_str, 1);
                $php_idx++;
        }
        return $eval_str;
}


function get_myc4RSS($status='publish') {

	$cat=get_option('myc4_cat');
	if($cat=="")$cat=array(1);
	else $cat=array($cat);

	$dir=dirname(__FILE__);

	// get the feeds
	$rss_url=get_option('myc4_feed_link');
	 
	# get rss file
	//$rss = @ fetch_rss($rss_url);
	$fc=file_get_contents($rss_url);


	$dom = new domDocument;
	@$dom->loadXML($fc);
	if (!$dom) {
	     echo "Error while parsing the document: $rss_url";
		exit;
	}
	$title_template=get_option('myc4_post_title');
	$post_template=get_option('myc4_post_content');

	$xpath = new DOMXPath($dom);
	$entries=$xpath->query('//item');
	$c=0;
	foreach ($entries as $entry) {
		$b=array();
		$b['name']=$b['title']=$entry->getElementsByTagName('title')->item(0)->nodeValue;
		$description=$entry->getElementsByTagName('description')->item(0)->nodeValue;
		$b['link']=$entry->getElementsByTagName('link')->item(0)->nodeValue;
		if(!preg_match('@Invest/Loans/View/([0-9]+)@',$b['link'],$m)){
			echo "Error while parsing myc4 link for opportunity id. the link is: ".$b['link']."<br />";
			exit;
		}
		$b['id']=$m[1];
	
		$posts=get_posts('meta_key=myc4_id&meta_value='.$b['id']);	
		if(count($posts)){
			continue;
		}
			
		$d2=new domDocument;
		@$d2->loadHTML($description);
		
		if (!$d2) {
		     	echo 'Error while parsing the description';
			exit;
		}
		$xp2=new DOMXPath($d2);
		$ents=$xp2->query('//table/tr[1]/td[2]');
		$b['date']=$ents->item(0)->nodeValue;
		
		$ents=$xp2->query('//table/tr[2]/td[2]');
		$b['amount']=$ents->item(0)->nodeValue;
	
		$ents=$xp2->query('//table/tr[3]/td[2]');
		$b['irate']=$ents->item(0)->nodeValue;

		$ents=$xp2->query('//table/tr[4]/td[2]');
		$b['status']=$ents->item(0)->nodeValue;
	
		$ents=$xp2->query('//table/tr[5]/td[2]');
		$b['comment']=$ents->item(0)->nodeValue;

		if($b['status']=='Active'){
			$fc=file_get_contents($b['link']);
			$d3=new domDocument;
			@$d3->loadHTML($fc);
			if(!$d3){
				echo "error while parsing op page: ${b['link']}";
				exit;
			}
			$xp3= new DOMXPath($d3);
			$ents=$xp3->query('//*[@class="investBox"]/div[@class="leftDiv"]/div/@style');
			$st=$ents->item(0)->nodeValue;
			$avurl="";
			if(preg_match("@'([^']+)'@",$st,$m)){
				$b['avurl']=$m[1];
			}
			$ents=$xp3->query('//img[@id="ctl00_ctl00_Middle_Main_uiCountryFlag"]/@src');
			$b['cflag']=$ents->item(0)->nodeValue;
			
			$ents=$xp3->query('//img[@id="ctl00_ctl00_Middle_Main_uiCountryFlag"]/@title');
			$b['cname']=trim($ents->item(0)->nodeValue);
		
			$ents=$xp3->query('//*[@class="oppertunityName"]/text()');
			foreach ($ents as $entry) {
				$b['bname'].=$entry->nodeValue;
			}
			$b['bname']=trim($b['bname']);
			
			$b['bname']=trim($ents->item(0)->nodeValue);
			$ents=$xp3->query('//*[@class="oppertunityBodyText"]/text()');
			foreach ($ents as $entry) {
				$b['shortDesc'].=$entry->nodeValue;
			}
			$b['shortDesc']=trim($b['shortDesc']);

			// get background details
			$fc=file_get_contents($b['link']."/Background");
			$d3=new domDocument;
			@$d3->loadHTML($fc);
			if(!$d3){
				echo "error while parsing op background page: ${b['link']}/Background";
				exit;
			}
			$xp3= new DOMXPath($d3);
			$ents=$xp3->query('//*[@id="centertext"]/div[@class="Panel"]/div/text()');
			$b['longDesc']=trim(trim($ents->item(1)->nodeValue,':'));
			
			$ents=$xp3->query('//*[@id="ctl00_ctl00_Middle_Main_uiBusinessView_pictures"]/ul/li/@style');
			$bitmp=$ents->item(0)->nodeValue;
			if(preg_match('/src=([^\)]+)/',$bitmp,$m)){	
				$b['bigImage']=urldecode($m[1]);
			}



			//echo nl2br(print_r($b,true));	
		
			$b['smallimg']=get_bloginfo('wpurl')."/wp-content/plugins/myc4-import".$b['avurl'];
			$b['largeimg']=get_bloginfo('wpurl')."/wp-content/plugins/myc4-import".$b['bigImage'];
			$b['cimg']=get_bloginfo('wpurl')."/wp-content/plugins/myc4-import".$b['cflag'];
			
			myc4_get_image($dir,$b['avurl']);
			myc4_get_image($dir,$b['bigImage']);
			myc4_get_image($dir,$b['cflag']);
	

			$p['post_title']=myc4_eval($title_template,$b);
			$p['post_content']=myc4_eval($post_template,$b);

			$p['post_status'] = $status;
			$p['post_date']=myc4_bdate($b['date']);
			$p['post_category']=$cat;
			if($pid=wp_insert_post($p)){
				add_post_meta($pid,'myc4_id',$b['id']);
				$np['ID']=$pid;
				$np['post_date']=myc4_bdate($b['date']);
				wp_update_post($np);
			}
			$c++;
		

		//	break;

		}


	}


	if($c)echo "Imported $c bids";
	else echo "No new bids found";

} 

function myc4_subpanel()
{
	if(get_option('myc4_post_title')==""){
		update_option('myc4_post_title','MYC4 profile <?=$b[\'name\']?>');
	}
	if(get_option('myc4_post_content')==""){
		update_option('myc4_post_content','
<table align="left" padding="2">
<tr><td><img src="<?=$b[\'smallimg\']?>" /></td></tr>
<tr><td><img src="<?=$b[\'cimg\']?>" title="<?=$b[\'cname\']?>" /> <?=$b[\'cname\']?></td></tr>
</table>
<?=$b[\'shortDesc\']?>
<!--more-->
<p><?=$b[\'longDesc\']?></p>
<img src="<?=$b[\'largeimg\']?>" />
<h3>My bid</h3>
Bid Amount: <?=$b[\'amount\']?><br />
Interest Rate: <?=$b[\'irate\']?><br />
<a href="<?=$b[\'link\']?>">Place your investment here</a>
');
	}

	if(isset($_POST['save_myc4_settings']) || isset($_POST['publish_myc4'])){
		update_option('myc4_feed_link',$_POST['myc4_feed_link']);
		update_option('myc4_cat',$_POST['myc4_cat']);		
		update_option('myc4_post_title',stripslashes($_POST['myc4_post_title']));
		update_option('myc4_post_content',stripslashes($_POST['myc4_post_content']));
		update_option('myc4_autoimport',stripslashes($_POST['myc4_autoimport']));
		if($_POST['myc4_autoimport']!='No'){
			$aa=date('Y');
			preg_match('/^[0-9]{4}$/',$_POST['aa']) and $aa=$_POST['aa'];
			$mm=date('m');
			preg_match('/^[0-9]{2}$/',$_POST['mm']) and $mm=$_POST['mm'];
			$dd=date('d');
			preg_match('/^[0-9]{2}$/',$_POST['jj']) and $dd=$_POST['jj'];
			
			$hh=date('H');
			preg_match('/^[0-9]{2}$/',$_POST['hh']) and $hh=$_POST['hh'];
			$mn=date('i');
			preg_match('/^[0-9]{2}$/',$_POST['mn']) and $mn=$_POST['mn'];
			$ss=date('s');
			preg_match('/^[0-9]{2}$/',$_POST['ss']) and $ss=$_POST['ss'];
			
			$dat="$aa-$mm-$dd $hh:$mn:$ss";
			update_option('myc4_autoimport_date',$dat);
			
			$ts=mysql2date('U',$dat);
			wp_clear_scheduled_hook('myc4_import_event');
			wp_schedule_event( $ts, stripslashes($_POST['myc4_autoimport']), 'myc4_import_event' );
		}else{
			wp_clear_scheduled_hook('myc4_import_event');
		}
	}

	
?>
	<div class="wrap">
		<h2>MYC4 bid Importer Settings</h2>
		
		<form method="post">
		<table class="form-table">
			<tr valign="top">
			<th scope="row">MYC4 feed link:</th>
      			<td>
			<input name="myc4_feed_link" type="text" id="myc4_feed_link" value="<?php echo get_option('myc4_feed_link'); ?>" size="64" />
			</td>
         		</tr>
         		<tr valign="top">
		        <th scope="row">Import in Category:</th>
          		<td>
			<select name="myc4_cat" > 
				<option value=""><?php echo attribute_escape(__('Select Category')); ?></option> 
				<?php 
					$categories=  get_categories('hide_empty=0'); 
					foreach ($categories as $cat) {
					  	$option = '<option value="'.$cat->cat_ID.'" '.((get_option('myc4_cat')==$cat->cat_ID)?"selected":"").' >';
						$option .= $cat->cat_name;
						$option .= ' ('.$cat->category_count.')';
						$option .= '</option>';
						echo $option;
  					}
				 ?>
			</select>
			</td>
			</tr>
			<tr valign="top">
			<th scope="row">Auto Import</td>
			<td><select  name="myc4_autoimport" id="myc4_autoimport">
				<?php
				$schedules=array('No','hourly','twicedaily','daily');
				$myc4_autoimport=get_option('myc4_autoimport');
				foreach($schedules as $s){ ?>
					<option <?php if($myc4_autoimport == $s){ echo "selected"; }?> ><?=$s?></option>
					
				<? } ?>
	Start at:
	<?php
		global $post;
		$pd=get_option('myc4_autoimport_date');
		if($pd=="")$post->post_date=date('Y-m-d H:i:s');
		else	$post->post_date=$pd;
		touch_time(1,1,0,1); 
	?>
				</td>
			</tr>
			<tr valign="top">
			<th scope="row">Post title template:</th>
      			<td>
			<input name="myc4_post_title" type="text" id="myc4_post_title" value="<?php echo get_option('myc4_post_title'); ?>" size="64" />
			</td>
         		</tr>

			<tr valign="top">
			<th scope="row">Post content template:</th>
      			<td>
			<textarea cols="60" rows="10" name="myc4_post_content" id="myc4_post_content" ><?php echo get_option('myc4_post_content'); ?></textarea>
			</td>
         		</tr>
         	
		</table>

        	<div class="submit">
	        	<input type="submit" name="save_myc4_settings" value="<?php _e('Save Settings', 'save_myc4_settings') ?>" />
	<!--		<input type="submit" name="import_myc4" value="<?php _e('Import Bids', 'myc4_import_bids') ?>" /> -->
			<input type="submit" name="publish_myc4" value="<?php _e('Import and Publish Bids', 'myc4_import_and_publish_bids') ?>" />
	        </div>
		<div align="center">Plugin developed by <a href="http://patchlog.com">Mihai Secasiu</a> for the <a href="http://microfinance.fm">Microfinance</a> blog of <a href="http://david.fm">prof. David Costa</a></div>
	</form>
<?
	/*if(isset($_POST['import_myc4'])){
		get_myc4RSS('pending');
	}else */
	if(isset($_POST['publish_myc4'])){
		get_myc4RSS('publish');
	}
}


function myc4_admin_menu() {
   if (function_exists('add_options_page')) {
        add_options_page('MYC4 Importer', 'MYC4 Import', 8, basename(__FILE__), 'myc4_subpanel');
        }
}

add_action('admin_menu', 'myc4_admin_menu'); 
register_deactivation_hook(__FILE__, 'myc4_deactivation');
add_action('myc4_import_event', 'get_myc4RSS');
function myc4_deactivation() {
	wp_clear_scheduled_hook('myc4_import_event');
}



