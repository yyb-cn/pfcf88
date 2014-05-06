<?php echo $this->fetch('inc/header.html'); ?> 
<?php
$this->_var['indexcss'][] = $this->_var['TMPL_REAL']."/css/index.css";
?>
<link rel="stylesheet" type="text/css" href="<?php 
$k = array (
  'name' => 'parse_css',
  'v' => $this->_var['indexcss'],
);
echo $k['name']($k['v']);
?>" />
<link rel="stylesheet" type="text/css" href="<?php echo $this->_var['TMPL']; ?>/css/main.css" />
</div>
<script  type="text/javascript"  src="<?php echo $this->_var['TMPL']; ?>/js/flash_.js"></script>

<!--图片切换开始-->
	<div  class="banner">

        <?php 
$k = array (
  'name' => 'index_login_box',
);
echo $this->_hash . $k['name'] . '|' . base64_encode(serialize($k)) . $this->_hash;
?>
        
		<div  id="banner">
			<div  id="bannerpic">
				
					<div  style="display: none; background-image: url(<?php echo $this->_var['TMPL']; ?>/images/001.jpg); height: 350px; background-position: 50% 50%; background-repeat: no-repeat no-repeat;">
						<a  href=""  title=""></a>
					</div>
				
					<div  style="display: none; background-image: url(<?php echo $this->_var['TMPL']; ?>/images/002.jpg); height: 350px; background-position: 50% 50%; background-repeat: no-repeat no-repeat;">
						<a  href=""  title=""></a>
					</div>
				
					<div  style="display: block; background-image: url(<?php echo $this->_var['TMPL']; ?>/images/003.jpg); height: 350px; background-position: 50% 50%; background-repeat: no-repeat no-repeat;">
						<a  href=""  title=""></a>
					</div>
				
					<div  style="display: none; background-image: url(<?php echo $this->_var['TMPL']; ?>/images/004.jpg); height: 350px; background-position: 50% 50%; background-repeat: no-repeat no-repeat;">
						<a  href=""  title=""></a>
					</div>

				
			</div>
			<div  class="yuandian">
				<ul>
							<li  class=""></li>
							<li  class=""></li>
							<li  class="cur"></li>				
							<li  class=""></li>		
					
				</ul>
			</div>
		</div>
	</div>
 <!--图片切换结束-->   
 <div  class="main_top">
		<div  class="mt">
			<div  class="mt_left">
				<div  class="mt_first wx1">
					<img  src="<?php echo $this->_var['TMPL']; ?>/images/gao1.gif"  alt=""  style="cursor: pointer;" >
					<h2>高收益</h2>
					<p>
						<span>30倍</span>银行存款利息<br> 高达<span>15%</span>年化收益
					</p>
				</div>
				<div  class="mt_first mt_two wx2">
					<img  src="<?php echo $this->_var['TMPL']; ?>/images/an1.gif"  alt=""  style="cursor: pointer;" >
					<h2>安全保障</h2>
					<p>
						多重担保，<span>100%</span>本息安全<br> 多重监管，<span>100%</span>资金安全
					</p>
				</div>
				<div  class="mt_first mt_three wx3">
					<img  src="<?php echo $this->_var['TMPL']; ?>/images/liu1.gif"  alt=""  style="cursor: pointer;" >
					<h2>流动快</h2>
					<p>
                    90天内选定期限，<span>100元</span>起投<br>
年化收益<span>（8+N）%</span>
					</p>
				</div>
			</div>
			<script  type="text/javascript">
				$(".wx1 img").hover(function() {
					$(this).attr("src", "<?php echo $this->_var['TMPL']; ?>/images/gao1.gif");
				}, function() {

				});
				$(".wx2 img").hover(function() {
					$(this).attr("src", "<?php echo $this->_var['TMPL']; ?>/images/an1.gif");
				}, function() {

				});
				$(".wx3 img").hover(function() {
					$(this).attr("src", "<?php echo $this->_var['TMPL']; ?>/images/liu1.gif");
				}, function() {

				});
			</script>
			<div  class="mt_right">
				<div  class="shuju_left"></div>
				<div  class="shuju_right"></div>
				<div  class="shuju_top"></div>
				<div  class="shuju_mid">
					<h2>成交数据</h2>
					<span>累计总交易金额</span> <span><em>159,879,958</em>元</span> <span>已按期还本金额</span>
					<span><i>21,793,022</i>元</span> <span>为用户带来的收益 </span> <span><i>4,832,689</i>元</span>
				</div>
				<div  class="shuju_bottom"></div>
			</div>
		</div>
	</div>   
<div class="wrap">      
<div class="blank10"></div>
<div class="index_left f_l">
	<div class="gray_title clearfix">
		<div class="f_l f_dgray b">最新产品列表</div>
		<div class="f_r">
			<span style="font-size: 12px; font-weight: normal;"><a href="<?php
echo parse_url_tag("u:index|deals|"."".""); 
?>" title="<?php echo $this->_var['LANG']['MORE']; ?>">更多产品列表...</a></span>
		</div>
	</div>
	<div class="i_deal_list clearfix pr15 pl15">
		<?php $_from = $this->_var['deal_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'deal');$this->_foreach['deal'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['deal']['total'] > 0):
    foreach ($_from AS $this->_var['key'] => $this->_var['deal']):
        $this->_foreach['deal']['iteration']++;
?>
		<div class="item <?php if ($this->_var['key'] % 2 == 1): ?>item_1<?php endif; ?> clearfix" <?php if (($this->_foreach['deal']['iteration'] == $this->_foreach['deal']['total'])): ?>style="border-bottom:0"<?php endif; ?>>
        
        <!--
			<div class="icon f_l">
				<a href="<?php echo $this->_var['deal']['url']; ?>"><img src="<?php echo $this->_var['deal']['icon']; ?>" class="img_b" height="80" width="80"></a>
			</div>
            -->
			<div class="detail f_r" style="float:left;">
				<div class="tit b lh24 clearfix">
					<a href="<?php echo $this->_var['deal']['url']; ?>" class="f_l" <?php if ($this->_var['deal']['deal_status'] == 1): ?>style="color:#428dcb
" <?php elseif ($this->_var['deal']['deal_status'] == 2): ?>style="color:#c35977" <?php elseif ($this->_var['deal']['deal_status'] == 4): ?>style="color:#749e54" <?php elseif ($this->_var['deal']['deal_status'] == 5): ?>style="color:#ba052f" <?php endif; ?> >
					<?php echo $this->_var['deal']['color_name']; ?></a>
				</div>
				<div class="clearfix" style=" padding-top:10px;">
					<div class="info f_l" style="width:225px;">投资金额：</div>
					<div class="info info2 f_l" style="width:180px;">年利率：</div>
					<div class="info info3 f_l" style="width:100px;">投资期限：</div>
				</div>
                <div class="clearfix">
					<div class="info f_l" style="width:225px;"><span class="f_red" style=" font-size:20px;"><?php echo $this->_var['deal']['borrow_amount_format']; ?></span></div>
					<div class="info info2 f_l" style="width:180px;"><span class="f_red" style=" font-size:20px;"><?php echo $this->_var['deal']['rate']; ?> %</span></div>
					<div class="info info3 f_l" style="width:100px;"><span class="f_red" style=" font-size:20px;"><?php echo $this->_var['deal']['repay_time']; ?><?php if ($this->_var['deal']['repay_time_type'] == 0): ?>天<?php else: ?>个月<?php endif; ?></span></div>
				</div>
				<div class="clearfix">

					<div class="info info2 f_l clearfix" style="width:225px;">
						<div class="f_l">投资进度：</div>
						<div class="greenProcessBar progressBar f_l indexbar">						
							<p><?php 
$k = array (
  'name' => 'round',
  'v' => $this->_var['deal']['progress_point'],
  'f' => '0',
);
echo $k['name']($k['v'],$k['f']);
?>%</p>
							<div class="p">
								<div class="c" style="width: <?php 
$k = array (
  'name' => 'round',
  'v' => $this->_var['deal']['progress_point'],
  'f' => '2',
);
echo $k['name']($k['v'],$k['f']);
?>%;"></div>
							</div>
						</div>
					</div>
					<div class="info info3 f_l" style="width:180px;"><?php if ($this->_var['deal']['deal_status'] == 2): ?><span class="f_red">满标</font><?php elseif ($this->_var['deal']['deal_status'] == 4): ?><span class="f_red">还款中</font><?php elseif ($this->_var['deal']['deal_status'] == 5): ?>还款完毕<?php else: ?>还需：<span class="f_red"><?php echo $this->_var['deal']['need_money']; ?></span><?php endif; ?></div>
				</div>
                
                
                
			</div>
				<?php if ($this->_var['deal']['deal_status'] == 1): ?>
            <a style="background:none; border:none; padding:0px" href="<?php echo $this->_var['deal']['url']; ?>" class="btn-orange"><img style=" width:100px; height:100px;" src="<?php echo $this->_var['TMPL']; ?>/images/load.png" alt="" width="111px" height="34px"></a>
            <?php elseif ($this->_var['deal']['deal_status'] == 2): ?><!--满标-->
			<a style="background:none; border:none; padding:0px" href="javascript:;" class="btn-orange"><img style=" width:100px;height:100px;" src="<?php echo $this->_var['TMPL']; ?>/images/load_full.png" alt="" width="111px" height="34px"></a>
            <?php elseif ($this->_var['deal']['deal_status'] == 4): ?><!--还款中-->
            <a style="background:none; border:none; padding:0px" href="javascript:;" class="btn-orange"><img style=" width:100px;height:100px;" src="<?php echo $this->_var['TMPL']; ?>/images/load_in_progress.png" alt="" width="111px" height="34px"></a>
            <?php elseif ($this->_var['deal']['deal_status'] == 5): ?><!--已还清-->
            <a style="background:none; border:none; padding:0px" href="javascript:;" class="btn-orange"><img style=" width:100px;height:100px;" src="<?php echo $this->_var['TMPL']; ?>/images/load_done.png" alt="" width="111px" height="34px"></a>
             <?php endif; ?>
		</div>
		<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
	</div>
</div>
<div class="index_right f_r">
	<adv adv_id="首页右侧顶部广告" />
	<div class="r-block">
		<div class="gray_title clearfix">
			<div class="f_l f_dgray b">网站公告</div>
			<div class="f_r">
            	<div style="vertical-align: middle;_padding: 8px 0;">
	                <span style="font-weight: normal;">
	                    <a href="<?php
echo parse_url_tag("u:index|notice#list_notice|"."".""); 
?>"> 更多</a>
	                </span>
	                <span><img src="<?php echo $this->_var['TMPL']; ?>/images/more.gif" align="absmiddle" alt="<?php echo $this->_var['LANG']['MORE']; ?>" style="" title="<?php echo $this->_var['LANG']['MORE']; ?>"></span>
	            </div>
        	</div>
		</div>
		<div class="notice-list clearfix">
			<ul>
				<?php $_from = $this->_var['notice_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'notice');if (count($_from)):
    foreach ($_from AS $this->_var['notice']):
?>
                <li style="padding:2px 0;">
                    <span>
					<a href="<?php echo $this->_var['notice']['url']; ?>"><?php echo $this->_var['notice']['title']; ?></a>
					</span>
                </li>
				<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
	        </ul>
		</div>
	</div>
	<div class="blank10"></div>
	<?php 
$k = array (
  'name' => 'success_deal_list',
);
echo $this->_hash . $k['name'] . '|' . base64_encode(serialize($k)) . $this->_hash;
?>
	<!--使用技巧-->
	<!--<div class="blank10"></div>
	<div class="r-block">
		<div class="gray_title clearfix">
			<div class="f_l f_dgray b">使用技巧</div>
			<div class="f_r">
	        	<div style="vertical-align: middle;_padding: 8px 0;">
	                <span style="font-weight: normal;">
	                    <a href="<?php
echo parse_url_tag("u:index|usagetip|"."".""); 
?>"> <?php echo $this->_var['LANG']['MORE']; ?></a>
	                </span>
	                <span><img src="<?php echo $this->_var['TMPL']; ?>/images/more.gif" align="absmiddle" alt="<?php echo $this->_var['LANG']['MORE']; ?>" style="" title="<?php echo $this->_var['LANG']['MORE']; ?>"></span>
	            </div>
	    	</div>
		</div>
		<div class="clearfix" style="padding:5px 15px; height:auto">
			<ul>
			<?php $_from = $this->_var['use_tech_list']['list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'use');if (count($_from)):
    foreach ($_from AS $this->_var['use']):
?>
            <li class="f_l" style="width: 220px; padding: 2px; overflow:hidden;white-space:nowrap;text-overflow:ellipsis; -o-text-overflow:ellipsis;" >
				 · <a href="<?php
echo parse_url_tag("u:index|usagetip|"."id=".$this->_var['use']['id']."".""); 
?>"  title="<?php echo $this->_var['use']['title']; ?>"><?php echo $this->_var['use']['title']; ?></a>
				</li>
			<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
       		</ul>
		</div>
	</div>-->
    
    <!--行业新闻-->
    <div class="blank10"></div>
    <div class="r-block">
		<div class="gray_title clearfix">
			<div class="f_l f_dgray b">行业新闻</div>
			<div class="f_r">
	        	<div style="vertical-align: middle;_padding: 8px 0;">
	                <span style="font-weight: normal;">
	                    <a href="<?php
echo parse_url_tag("u:index|news|"."".""); 
?>"> <?php echo $this->_var['LANG']['MORE']; ?></a>
	                </span>
	                <span><img src="<?php echo $this->_var['TMPL']; ?>/images/more.gif" align="absmiddle" alt="<?php echo $this->_var['LANG']['MORE']; ?>" style="" title="<?php echo $this->_var['LANG']['MORE']; ?>"></span>
	            </div>
	    	</div>
		</div>
		<div class="clearfix" style="padding:5px 15px; height:auto">
			<ul>
			<?php $_from = $this->_var['news_list']['list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'news');if (count($_from)):
    foreach ($_from AS $this->_var['news']):
?>
            <li class="f_l" style="width: 230px; padding: 2px; overflow:hidden;white-space:nowrap;text-overflow:ellipsis; -o-text-overflow:ellipsis;" >
				 · <a href="<?php
echo parse_url_tag("u:index|news|"."id=".$this->_var['news']['id']."".""); 
?>"  title="<?php echo $this->_var['news']['title']; ?>"><?php echo $this->_var['news']['title']; ?></a>
				</li>
			<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
       		</ul>
		</div>
	</div>
</div>
<?php echo $this->fetch('inc/footer.html'); ?>