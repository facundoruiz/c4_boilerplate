<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\API\ResponseTrait;

use App\Models\MenuManagementModel;
use App\Models\RolesPermissionsModel;
use App\Models\Users;

class Authorization implements FilterInterface
{
	private $menuModel;
	private $permissionModel;
	
	public function __construct()
	{
		$this->menuModel = new MenuManagementModel();
		$this->permissionModel = new RolesPermissionsModel();
	}
	public function before(RequestInterface $request, $arguments = null)
	{
		helper('custom');
		
		$segment = $request->getUri()->getSegment(2);

		if ($segment) :
			$menu 		= $this->menuModel->getMenuByUrl($segment);

			if (!$menu) :
				//not found
				return view('errors/html/error_404');
			// return redirect()->to(base_url('/'));
			else :
				$dataAccess = [
					'roleID' => session()->get('vr_sess_user_role'),
					'menuID' => $menu['id']
				];
				$userAccess = $this->permissionModel->checkUserMenuAccess($dataAccess);

				if (!$userAccess && !is_superadmin()) :
					// not granted
					return redirect()->to(base_url('admin/blocked'));
				endif;
			endif;
		endif;
	}
	public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
	{
		//
	}
}
