<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
!defined('iPATH') && exit('What are you doing?'); 
?>
<div class="content-box">
  <div class="content-box-header">
    <h3>个人资料</h3>
  </div>
  <div class="content-box-content">
    <div class="tab-content default-tab" id="tab2">
      <form action="<?=__SELF__?>?do=setting&operation=post" method="post" enctype="multipart/form-data" name="profile" id="profile">
        <fieldset>
        <p>
          <label>用户名</label>
    		<input name="name" type="text" id="name" value="<?=$rs->username?>" readonly="true" class="text-input medium-input"/>
          <span>用户名禁止修改</span>
          </p>
        <p>
          <label>新密码</label>
          <input type="password" name="pwd1" class="text-input medium-input" id="pwd1" value="" />
    		<span>不更改请留空</span>
        </p>
        <p>
          <label>确认密码</label>
          <input type="password" name="pwd2" class="text-input medium-input" id="pwd2" value="" />
    	<span>不更改请留空</span>
        </p>
        <p>
          <label>注册时间</label>
          <input type="text" disabled value="<?=get_date($rs->info['regtime'],"Y-m-d H:i:s")?>" readonly="true" class="text-input medium-input" />
        </p>
        <p>
          <label>最后登陆IP</label>
          <input type="text" disabled value="<?=$rs->lastip?>" readonly="true" class="text-input medium-input" />
        </p>
        <p>
          <label>最后登陆时间</label>
          <input type="text" disabled value="<?=get_date($rs->lastlogintime,"Y-m-d H:i:s")?>" readonly="true" class="text-input medium-input" />
        </p>
        <p>
          <label>昵称</label>
          <input type="text" name="nickname" class="text-input medium-input" id="nickname" value="<?=$rs->info['nickname']?>" maxlength="12" />
    		<span>发表文章时显示的名字,留空显示用户名</span>
        </p>
        <p>
          <label>QQ/MSN</label>
          <input type="text" name="icq" class="text-input medium-input" id="icq" value="<?=$rs->info['icq']?>" maxlength="12" />
        </p>
        <p>
          <label>E-mail</label>
          <input type="text" name="email" class="text-input medium-input" id="email" value="<?=$rs->info['email']?>" maxlength="12" />
        </p>
        <p>
          <label>主页/博客</label>
          <input type="text" name="home" class="text-input medium-input" id="home" value="<?=$rs->info['home']?>" maxlength="12" />
        </p>
        <p>
          <label>主页/博客</label>
          <select name="gender" id="gender">
            <option value="2">保密</option>
            <option value="1">男</option>
            <option value="0">女</option>
          </select>
        </p>
        <p>
          <label>生日</label>
          <select name="year" id="year" style="width:60px;">
            <option value=""></option>
            <option value="1970">1970</option>
            <option value="1971">1971</option>
            <option value="1972">1972</option>
            <option value="1973">1973</option>
            <option value="1974">1974</option>
            <option value="1975">1975</option>
            <option value="1976">1976</option>
            <option value="1977">1977</option>
            <option value="1978">1978</option>
            <option value="1979">1979</option>
            <option value="1980">1980</option>
            <option value="1981">1981</option>
            <option value="1982">1982</option>
            <option value="1983">1983</option>
            <option value="1984">1984</option>
            <option value="1985">1985</option>
            <option value="1986">1986</option>
            <option value="1987">1987</option>
            <option value="1988">1988</option>
            <option value="1989">1989</option>
            <option value="1990">1990</option>
            <option value="1991">1991</option>
            <option value="1992">1992</option>
            <option value="1993">1993</option>
            <option value="1994">1994</option>
            <option value="1995">1995</option>
            <option value="1996">1996</option>
            <option value="1997">1997</option>
            <option value="1998">1998</option>
            <option value="1999">1999</option>
            <option value="2000">2000</option>
            <option value="2001">2001</option>
            <option value="2002">2002</option>
          </select>
          年
          <select name="month" id="month"  style="width:60px;">
            <option value=""></option>
            <option value="1">1</option>
            <option value="2">2</option>
            <option value="3">3</option>
            <option value="4">4</option>
            <option value="5">5</option>
            <option value="6">6</option>
            <option value="7">7</option>
            <option value="8">8</option>
            <option value="9">9</option>
            <option value="10">10</option>
            <option value="11">11</option>
            <option value="12">12</option>
          </select>
          月
          <select name="day" id="day"  style="width:60px;">
            <option value=""></option>
            <option value="1">1</option>
            <option value="2">2</option>
            <option value="3">3</option>
            <option value="4">4</option>
            <option value="5">5</option>
            <option value="6">6</option>
            <option value="7">7</option>
            <option value="8">8</option>
            <option value="9">9</option>
            <option value="10">10</option>
            <option value="11">11</option>
            <option value="12">12</option>
            <option value="13">13</option>
            <option value="14">14</option>
            <option value="15">15</option>
            <option value="16">16</option>
            <option value="17">17</option>
            <option value="18">18</option>
            <option value="19">19</option>
            <option value="20">20</option>
            <option value="21">21</option>
            <option value="22">22</option>
            <option value="23">23</option>
            <option value="24">24</option>
            <option value="25">25</option>
            <option value="26">26</option>
            <option value="27">27</option>
            <option value="28">28</option>
            <option value="29">29</option>
            <option value="30">30</option>
            <option value="31">31</option>
          </select>
          日
        </p>
        <p>
          <label>来自</label>
          <input type="text" name="from" class="text-input medium-input" id="from" value="<?=$rs->info['from']?>" maxlength="12" />
        </p>
        <p>
          <label>签名</label>
          <textarea class="text-input textarea wysiwyg" name="signature" id="signature" cols="50" rows="8"><?=$rs->info['signature']?></textarea>
        </p>
        <p>
          <input name="action" type="hidden" id="action" value="edit" />
          <input type="submit" value="提交" class="button" />
        </p>
        </fieldset>
        <div class="clear"></div>
        <!-- End .clear -->
      </form>
    </div>
  </div>
</div>
<script type="text/javascript">
$("#gender").val("<?=$rs->gender?>");
$("#year").val("<?=$rs->info['year']?>");
$("#month").val("<?=$rs->info['month']?>");
$("#day").val("<?=$rs->info['day']?>");
</script>
<?php /*
<div class="notification attention png_bg"> <a href="#" class="close"><img src="usercp/style/cross_grey_small.png" title="Close this notification" alt="close" /></a>
  <div> Attention notification. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin vulputate, sapien quis fermentum luctus, libero. </div>
</div>
<div class="notification information png_bg"> <a href="#" class="close"><img src="usercp/style/cross_grey_small.png" title="Close this notification" alt="close" /></a>
  <div> Information notification. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin vulputate, sapien quis fermentum luctus, libero. </div>
</div>
<div class="notification success png_bg"> <a href="#" class="close"><img src="usercp/style/cross_grey_small.png" title="Close this notification" alt="close" /></a>
  <div> Success notification. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin vulputate, sapien quis fermentum luctus, libero. </div>
</div>
<div class="notification error png_bg"> <a href="#" class="close"><img src="usercp/style/cross_grey_small.png" title="Close this notification" alt="close" /></a>
  <div> Error notification. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin vulputate, sapien quis fermentum luctus, libero. </div>
</div>
*/?>
