<?php if ($this->_var['succuess_deal_list']): ?>
<div class="r-block">
	<div class="gray_title clearfix">
		<div class="f_l f_dgray b">成功案例</div>
		<div class="f_r">
        	<div style="vertical-align: middle;_padding: 8px 0;">
                <span style="font-weight: normal;">
                    <a href="<?php
echo parse_url_tag("u:index|deals|"."cid=last".""); 
?>"> 更多</a>
                </span>
                <span><img src="<?php echo $this->_var['TMPL']; ?>/images/more.gif" align="absmiddle" alt="<?php echo $this->_var['LANG']['MORE']; ?>" style="" title="<?php echo $this->_var['LANG']['MORE']; ?>"></span>
            </div>
    	</div>
	</div>
	<div class="clearfix" style="padding:5px 12px;">
		<div id="examIndex" >
			<ul>
				<?php $_from = $this->_var['succuess_deal_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'deal_0_72883400_1399370810');if (count($_from)):
    foreach ($_from AS $this->_var['deal_0_72883400_1399370810']):
?>
    			<li class="clearfix"> 
    				 <div class="mt5">
    				 	<a href="<?php echo $this->_var['deal_0_72883400_1399370810']['url']; ?>" target="_blank"><?php echo $this->_var['deal_0_72883400_1399370810']['name']; ?></a>
					 </div>
                     <div class="f_l tl" style="width:80px"><?php echo $this->_var['deal_0_72883400_1399370810']['borrow_amount_format']; ?></div>
                     <div class="f_l tc" style="width:50px"><?php 
$k = array (
  'name' => 'number_format',
  'b' => $this->_var['deal_0_72883400_1399370810']['rate'],
  'f' => '2',
);
echo $k['name']($k['b'],$k['f']);
?>%</div>
                     <div class="f_l tc" style="width:50px"><?php echo $this->_var['deal_0_72883400_1399370810']['repay_time']; ?><?php if ($this->_var['deal_0_72883400_1399370810']['repay_time_type'] == 0): ?>天<?php else: ?>个月<?php endif; ?></div>
                     <div class="f_l tr" style="width:78px">[<?php 
$k = array (
  'name' => 'to_date',
  'v' => $this->_var['deal_0_72883400_1399370810']['success_time'],
  'f' => 'Y-m-d',
);
echo $k['name']($k['v'],$k['f']);
?>]</div>	
    			</li>
				<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
    	   </ul>
		</div>
	</div>
</div>
<?php endif; ?>