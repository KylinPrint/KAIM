<?php

namespace App\Admin\Actions\Grid;

use Dcat\Admin\Actions\Response;
use Dcat\Admin\Grid;
use Dcat\Admin\Grid\Tools\AbstractTool;
use Dcat\Admin\Traits\HasPermissions;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class PbindTemplateExportTool extends AbstractTool
{

    protected $style = 'btn btn-outline-info';

    protected $grid;

    public function __construct($filename = null, $title = '',$grid = '')
    {
        parent::__construct($title);
        $this->grid = $grid;
        $this->title = $title;
        $this->filename = $filename;
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
        $filename = $request->get('filename');
        $cururl = $request->get('cururl');
        
        return 
        $this->response()
            ->download(admin_route('pbind-template.export', 
            [
                'filename' => $filename,
                'cururl'   => $cururl,
            ]));
    }

    protected function actionScript()
    {
        return <<<JS
        function (data, target, action) {
            var url;
            url = window.location.search;
            url = url.slice(1);
            data.cururl = url;
        }
        JS;
    }

    /**
     * @return string|void
     */
    protected function href()
    {
        // return admin_url('auth/users');
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
        return [
            'filename' => $this->filename,
        ];
    }

}
