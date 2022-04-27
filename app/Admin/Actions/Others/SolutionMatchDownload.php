<?php

namespace App\Admin\Actions\Others;

use Dcat\Admin\Actions\Response;
use Dcat\Admin\Traits\HasPermissions;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Dcat\Admin\Grid\RowAction;
use Illuminate\Http\Request;
use App\Models\SolutionMatch;

class SolutionMatchDownload extends RowAction
{
    
    /**
     * @return string
     */
	public function title()

    {
        return '<i class="feather icon-download"></i> 下载';
    }

    /**
     * Handle the action request.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function handle(Request $request)
    {
        $id = $this->getKey();
        
        $filePath = SolutionMatch::find($id)->title;

        return $this->response()->download(url('storage/solution-match/'.$filePath));
    }

    /**
     * @return string|array|void
     */


    
    public function confirm()
    {
        // return ['Confirm?', 'contents'];
    }

    /**
     * @param Model|Authenticatable|HasPermissions|null $user
     *
     * @return bool
     */
    protected function authorize($user): bool
    {
        return true;
    }

    /**
     * @return array
     */
    protected function parameters()
    {
        return [];
    }
}