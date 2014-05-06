<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta name="Generator" />
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php if ($this->_var['page_title']): ?><?php echo $this->_var['page_title']; ?> - <?php endif; ?><?php echo $this->_var['site_info']['SHOP_TITLE']; ?></title>
<link rel="icon" href="favicon.ico" type="/image/x-icon" />
<link rel="shortcut icon" href="favicon.ico" type="/image/x-icon" />
<meta name="keywords" content="<?php if ($this->_var['page_keyword']): ?><?php echo $this->_var['page_keyword']; ?><?php endif; ?><?php echo $this->_var['site_info']['SHOP_KEYWORD']; ?>" />
<meta name="description" content="<?php if ($this->_var['page_description']): ?><?php echo $this->_var['page_description']; ?><?php endif; ?><?php echo $this->_var['site_info']['SHOP_DESCRIPTION']; ?>" />
<link rel="stylesheet" type="text/css" href="<?php echo $this->_var['TMPL']; ?>/css/comm.css" />
<link rel="stylesheet" type="text/css" href="<?php echo $this->_var['TMPL']; ?>/css/flash-banner.css" />
<script type="text/javascript" src="<?php echo $this->_var['TMPL']; ?>/js/jquery-1.4.4.min.js"></script>
<script type="text/javascript" src="<?php echo $this->_var['TMPL']; ?>/js/index_.js"></script>
<?php
$this->_var['pagecss'][] = $this->_var['TMPL_REAL']."/css/style.css";
$this->_var['pagecss'][] = $this->_var['TMPL_REAL']."/css/weebox.css";
$this->_var['pagejs'][] = $this->_var['TMPL_REAL']."/js/jquery.js";
$this->_var['pagejs'][] = $this->_var['TMPL_REAL']."/js/jquery.bgiframe.js";
$this->_var['pagejs'][] = $this->_var['TMPL_REAL']."/js/jquery.weebox.js";
$this->_var['pagejs'][] = $this->_var['TMPL_REAL']."/js/jquery.pngfix.js";
$this->_var['pagejs'][] = $this->_var['TMPL_REAL']."/js/lazyload.js";
$this->_var['pagejs'][] = $this->_var['TMPL_REAL']."/js/script.js";
$this->_var['pagejs'][] = $this->_var['TMPL_REAL']."/js/op.js";
$this->_var['cpagejs'][] = $this->_var['TMPL_REAL']."/js/script.js";
$this->_var['cpagejs'][] = $this->_var['TMPL_REAL']."/js/op.js";
if(app_conf("APP_MSG_SENDER_OPEN")==1)
{
$this->_var['pagejs'][] = $this->_var['TMPL_REAL']."/js/msg_sender.js";
$this->_var['cpagejs'][] = $this->_var['TMPL_REAL']."/js/msg_sender.js";
}
$this->_var['pagecss'][] = $this->_var['TMPL_REAL']."/css/main.css";
?>
<link rel="stylesheet" type="text/css" href="<?php 
$k = array (
  'name' => 'parse_css',
  'v' => $this->_var['pagecss'],
);
echo $k['name']($k['v']);
?>" />

<script type="text/javascript">
var APP_ROOT = '<?php echo $this->_var['APP_ROOT']; ?>';;
var LOADER_IMG = '<?php echo $this->_var['TMPL']; ?>/images/lazy_loading.gif';
var ERROR_IMG = '<?php echo $this->_var['TMPL']; ?>/images/image_err.gif';
<?php if (app_conf ( "APP_MSG_SENDER_OPEN" ) == 1): ?>
var send_span = <?php 
$k = array (
  'name' => 'app_conf',
  'v' => 'SEND_SPAN',
);
echo $k['name']($k['v']);
?>000;
<?php endif; ?>
</script>
<script type="text/javascript" src="<?php echo $this->_var['APP_ROOT']; ?>/public/runtime/app/lang.js"></script>
<script type="text/javascript" src="<?php 
$k = array (
  'name' => 'parse_script',
  'v' => $this->_var['pagejs'],
  'c' => $this->_var['cpagejs'],
);
echo $k['name']($k['v'],$k['c']);
?>"></script>

</head>

<body>
<?php if ($this->_var['vote']): ?>
<a id="vote" href="<?php
echo parse_url_tag("u:index|vote|"."".""); 
?>" target="_blank"></a>
<?php endif; ?>
<div class="header" id="header">
	<div class="wrap constr">
		<div class="wrap clearfix">
			<div class="logo f_l pt15">
			<a class="link f_l" href="<?php echo $this->_var['APP_ROOT']; ?>/">
				<?php
					$this->_var['logo_image'] = app_conf("SHOP_LOGO");
				?>
				<?php 
$k = array (
  'name' => 'load_page_png',
  'v' => $this->_var['logo_image'],
);
echo $k['name']($k['v']);
?>
			</a>
			</div>
			<div class="f_r">
				<span id="user_head_tip" class="pr">
				<?php 
$k = array (
  'name' => 'load_user_tip',
);
echo $this->_hash . $k['name'] . '|' . base64_encode(serialize($k)) . $this->_hash;
?>
				<span class="li"><a href="<?php
echo parse_url_tag("u:index|helpcenter|"."".""); 
?>">帮助</a></span><span class="li">客服电话：<?php 
$k = array (
  'name' => 'app_conf',
  'v' => 'SHOP_TEL',
);
echo $k['name']($k['v']);
?></span>
				</span>

			</div>
            <div class="hd-tel"></div>		
		</div><!--end wrap-->
		
	</div>





</div>
	<div  class="headf">
	<div  class="head_main">
		<a  href="<?php echo $this->_var['APP_ROOT']; ?>/index.php"  title=""> <img  src="<?php echo $this->_var['TMPL']; ?>/images/logo.jpg" width="365" height="70"  alt=""></a>
		<ul>
            <!-- <li><a href="<?php echo $this->_var['APP_ROOT']; ?>">首页</a></li> -->
            <?php $_from = $this->_var['nav_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'nav_item');if (count($_from)):
    foreach ($_from AS $this->_var['key'] => $this->_var['nav_item']):
?>
			<li  class="onn"><a  title=""  style="padding: 10px 0; cursor: pointer;"  href="<?php echo $this->_var['nav_item']['url']; ?>" target="<?php if ($this->_var['nav_item']['blank'] == 1): ?>_blank<?php endif; ?>"><?php echo $this->_var['nav_item']['name']; ?></a>
            <?php if ($this->_var['nav_item']['sub_nav']): ?>
				<ul  style="display: none;">
                    <?php $_from = $this->_var['nav_item']['sub_nav']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'sub_item');if (count($_from)):
    foreach ($_from AS $this->_var['sub_item']):
?>
					<li><a  href="<?php echo $this->_var['sub_item']['url']; ?>"  title="" target="<?php if ($this->_var['sub_item']['blank'] == 1): ?>_blank<?php endif; ?>"><?php echo $this->_var['sub_item']['name']; ?></a></li>
                    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
				</ul>
            <?php endif; ?>
            </li>
            
           <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
		</ul>
	</div>
</div>

<div class="wrap">
