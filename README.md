pfcf88
======
春秋在前还是战国在前？

Our own p2p-like website

版本主体：
战国      zhanguo
秦朝      qin
西汉      xihan
东汉      donghan
三国      sanguo
西晋      xijin
东晋      dongjin
南北朝    nanbei
隋朝      sui
唐朝      tang
五代十国  wudai
宋朝      song
元朝      yuan
明朝      ming
清朝      qing
1.pfcf88.com是一个p2e平台,类似于余额宝,采用了方维p2p作为基础来进行二次开发的。
2.pfcf88.com采用了thinkphp框架作为后台开发，双入口，前台却并不是完全的thinkphp
3.产品列表页：无刷新分页,加载loading效果,无刷新条件筛选,ajax,jquery,
4.代金券，计算利息方法：还款金额=本金+((本金+代金券)*利息)*n个月/12
如果是按天计算(deal_time_type=0)：还款金额=本金+((本金+代金券)*利息)*n天/365
5.首页产品推荐，根据分类排序(sort)来排列，显示最大的三个，名字是该分类产品的名称和天数和利率。
6.几个单页面，产品介绍，公司介绍，办公环境。
7.产品资料，资料下载(pdf)，图片在线预览。
后台：
1.一键满标
2.代金券模块:给会员发送代金券,添加代金券，编辑代金券
3.交易列表,会员名,真实姓名,电话,项目名称,交易金额,利率(rate),期限,交易时间.(关联了三个表:user,deal,deal_load)
4.认购系统:数据传输,采用地址栏传输。

