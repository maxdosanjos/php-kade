<?php
namespace Kade\Tests\Http;

/**
 * Quem pode chamar cada ação dos controllers (AbstractCtrl::guardAction).
 */
class AccessControlTest extends HttpTestCase
{
	/** @dataProvider adminActions */
	public function testAdminActionsRejectVisitors($path)
	{
		$response = $this->anonymous()->post($path);
		$this->assertSame(401, $response->status, $path);
		$this->assertJsonError($response, $path);
	}

	/** @dataProvider adminActions */
	public function testAdminActionsRejectCommonUsers($path)
	{
		$response = $this->user()->post($path);
		$this->assertSame(403, $response->status, $path);
		$this->assertJsonError($response, $path);
	}

	public function adminActions()
	{
		return array(
			array('/AccountQueryCtrl.ctrlExt/searchArray'),
			array('/AccountRegCtrl.ctrlExt/saveInter'),
			array('/AccountRegCtrl.ctrlExt/cancelCont'),
			array('/AccountRegCtrl.ctrlExt/incParc'),
			array('/CustumerQueryCtrl.ctrlExt/searchArray'),
			array('/CustumerQueryCtrl.ctrlExt/exportCSV'),
			array('/CustumerRegCtrl.ctrlExt/edit'),
			array('/MsgLogQueryCtrl.ctrlExt/searchArray'),
			array('/MapsExtCtrl.ctrlExt/loadBanner'),
			array('/MapsExtCtrl.ctrlExt/removeBanner'),
			array('/MapsExtCtrl.ctrlExt/expiredRegisters'),
			array('/VehicleQueryCtrl.ctrlExt/exportCSV'),
			array('/AccountRegCtrl.ctrlExt/acaoQueNaoExiste'),
		);
	}

	/** @dataProvider userActions */
	public function testUserActionsRejectVisitors($path)
	{
		$response = $this->anonymous()->post($path);
		$this->assertSame(401, $response->status, $path);
		$this->assertJsonError($response, $path);
	}

	public function userActions()
	{
		return array(
			array('/InstallmentQueryCtrl.ctrlExt/searchArray'),
			array('/VehicleQueryCtrl.ctrlExt/searchArray'),
			array('/VehicleQueryCtrl.ctrlExt/viewDetail'),
			array('/VehicleQueryCtrl.ctrlExt/setStsUtilized'),
			array('/VehicleQueryCtrl.ctrlExt/setStsCancel'),
			array('/CustumerRegCtrl.ctrlExt/saveInter'),
		);
	}

	/** @dataProvider publicActions */
	public function testPublicActionsAreOpen($path)
	{
		$this->assertSame(200, $this->anonymous()->get($path)->status, $path);
	}

	public function publicActions()
	{
		return array(
			array('/MapsExtCtrl.ctrlExt/searchMaps'),
			array('/MapsExtCtrl.ctrlExt/searchMapsByUf?uf=PR'),
			array('/AddressRegCtrl.ctrlExt/searchCEP?zipcode=86010000'),
			array('/CustumerRegCtrl.ctrlExt/getCaptcha'),
		);
	}

	public function testAdminCanReachAdminActions()
	{
		$response = $this->admin()->get('/CustumerQueryCtrl.ctrlExt/exportCSV');
		$this->assertSame(200, $response->status);
		$this->assertStringContainsString('text/csv', $response->header('content-type'));
	}

	public function testCommonUserCannotSeeAnotherUsersVehicleCancelled()
	{
		// Cancelar veículo de outra pessoa: o dono é conferido no banco
		$vehicleId = \Kade\Tests\Support\Db::value(
			"SELECT vc.vehicle_traveling_id FROM vehicle_contact vc JOIN user u ON u.person_id <> vc.person_id
			  WHERE u.login = ? LIMIT 1", array(getenv('KADE_TEST_USER_LOGIN')));
		if (!$vehicleId)
			$this->markTestSkipped('Nenhum veículo de outra pessoa no banco de teste');
		$before = \Kade\Tests\Support\Db::value("SELECT status FROM vehicle_traveling WHERE id = ?", array($vehicleId));

		$response = $this->user()->post('/VehicleQueryCtrl.ctrlExt/setStsCancel', array('vehicle_id' => $vehicleId, 'id' => $vehicleId));
		$this->assertJsonError($response);
		$this->assertSame($before, \Kade\Tests\Support\Db::value("SELECT status FROM vehicle_traveling WHERE id = ?", array($vehicleId)));
	}
}
