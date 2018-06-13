<?php
/**
 * Created by PhpStorm.
 * User: ellison
 * Date: 2018/5/28
 * Time: 5:49 PM
 */

namespace App\Http\Controllers\Community;

use App\Models\CheckCodeManager;
use App\Models\Commander;
use App\Models\CommanderOrder;
use App\Models\CommanderWithdrawRecord;
use App\Models\Company;
use App\Models\CustomerService;
use App\Models\Delivery;
use App\Models\Group;
use App\Models\GroupDetail;
use App\Models\GroupDetailPicture;
use App\Models\GroupOrder;
use App\Models\GroupOrderDetail;
use App\Models\GroupUserClick;
use App\Models\PayConfig;
use App\Models\User;
use App\Models\UserAddress;
use App\Http\Controllers\Community\UserRecommend;

class CommunityUserDelete
{
    public function SmallUserDelete($userid)
    {
        if ( !$userid) {
            return false;
        }

        CommunityCheckCodeManager::where('community_small_id', $userid)->delete();
        CommunityCommander::where('community_small_id', $userid)->delete();
        CommunityCommanderOrder::where('community_small_id', $userid)->delete();
        CommunityCommanderWithdrawRecord::where('community_small_id', $userid)->delete();
        CommunityCompany::where('community_small_id', $userid)->delete();
        CommunityCustomerService::where('community_small_id', $userid)->delete();
        CommunityDelivery::where('community_small_id', $userid)->delete();
        CommunityGroup::where('community_small_id', $userid)->delete();
        CommunityGroupDetail::where('community_small_id', $userid)->delete();
        CommunityGroupDetailPicture::where('community_small_id', $userid)->delete();
        CommunityGroupOrder::where('community_small_id', $userid)->delete();
        CommunityGroupOrderDetail::where('community_small_id', $userid)->delete();
        CommunityGroupUserClick::where('community_small_id', $userid)->delete();
        CommunityPayConfig::where('community_small_id', $userid)->delete();
        CommunityUser::where('community_small_id', $userid)->delete();
        CommunityUserAddress::where('community_small_id', $userid)->delete();
        CommunityUserRecommend::where('community_small_id', $userid)->delete();

        $small = SmallUser::find($userid);
        if (count($small) > 0) {
            \Log::error("删除社区团购 smalluser 信息：$userid");
            $oldlogo = str_replace('https://www.ailetugo.com/ailetutourism', '../', $small->app_url);
            $this->deletefile($oldlogo);
            $small->delete();
        }

        return true;
    }

    //删除图片
    private function deletefile($file)
    {
        if (file_exists($file)) {
            \Log::error('删除文件：' . $file);
            unlink($file);
        }
    }
}