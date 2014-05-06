<?php if ($this->_var['user_info']): ?>

	<div  class="denglu">
			<div  class="denglu_maintwo"></div>
			<form  name="loginForm"  action=""  method="post" >
				<input  type="hidden"  name="failNum"  value=""> <input  type="hidden"  id="username"  name="j_username">
				<div  class="denglu_main1">
						<h2 style="border-bottom:1px #FFF solid;">
							欢迎登陆浦发财富
						</h2>
						<ul>
                            <li class="dq">您正在使用的账号:</li>
							<li  class="dqzh">用户名：<span class="yhm"><?php echo $this->_var['user_info']['user_name']; ?></span></li>
							<li  class="dqzh">用户余额：<span class="yhm"><?php echo format_price($this->_var['user_info']['money'] + $this->_var['user_info']['lock_money']); ?></span></li>
							<li><div class="denglut"><a id="haerder_out"  href="<?php
echo parse_url_tag("u:shop|uc_center|"."".""); 
?>">用户中心</a></div></li>
						</ul>
						<p>
						<div class="dldb"><a href="index.php?ctl=deals">投资</a></div><div class="dldb"><a href="/index.php?ctl=uc_money&act=incharge">充值</a></div><div class="dldb" style=" border:none;"><a href="/index.php?ctl=uc_money&act=carry">提现</a></div>
						</p>	
				</div>
			</form>
		</div>	 
   <?php else: ?>
	<div  class="denglu">
			<div  class="denglu_main"  style="height: 279px;"></div>
			<form method="post" action="<?php
echo parse_url_tag("u:shop|user#dologin|"."".""); 
?>" name="ajax_login_form" >
				<input  type="hidden"  name="failNum"  value=""> <input  type="hidden"  id="username"  name="j_username">
				<div  class="denglu_main1">
					
						<h2>
							登录浦发财富<span  id="errorTip"></span>
						</h2>
						<ul>
							<li  class="name"><input type="text" value=""  name="email"
 id="j_username"></li>
							<li  class="mima"><input type="password" value=""  name="user_pwd"
                                id="j_password"  ></li>
							
							<li  class="wj"><a  href="<?php
echo parse_url_tag("u:index|user#getpassword|"."".""); 
?>"  title=""><?php echo $this->_var['LANG']['FORGET_PASSWORD']; ?></a></li>
							<li><input  type="submit" id="ajax-login-submit" name="commit" value="<?php echo $this->_var['LANG']['LOGIN']; ?>"   class="denglu"></li>
							<!--<li><a  href=""  title=""  class="w">微博登录</a><a  href=""  title="QQ登陆"  class="q">QQ登录</a></li>-->
						</ul>
						<p>
							您还不是浦发会员？<a  href=""  title="">马上注册!</a>
						</p>	
				</div>
			</form>
		</div>	
    <?php endif; ?>

      