<?php
namespace Kuyuan\WqCore\plug\defend;
use Kuyuan\WqCore\controller\Controller;
use Kuyuan\WqCore\plug\defend\model\W7KuyuanDefendDomainModel;

/**
 * 酷源防封
 * Class Defend
 * @package Core\plug\defend
 */
class Defend extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 测试是否可以使用防封
     */
    public function test()
    {
        return pdo_tableexists("kuyuan_defend_domain");
    }

    /**
     * 获取防封域名
     */
    public function getDomain()
    {
        $_domains = W7KuyuanDefendDomainModel::field(array("domain"))->select();
        if($_domains){
            $_domain = $_domains[array_rand((array)$_domains,1)];
            $_query = $_SERVER['QUERY_STRING'];
            return array('domain'=>$_domain['domain'],'url'=>urldecode($_domain['domain'].$_SERVER['PHP_SELF'].'?defend_jump=1&'.$_query));
        }else{
            return false;
        }
    }
}