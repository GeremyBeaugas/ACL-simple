<?php
namespace controllers;
use Ubiquity\attributes\items\router\Get;

use Ubiquity\attributes\items\acl\Allow;
use Ubiquity\attributes\items\acl\Permission;
use Ubiquity\attributes\items\acl\Resource;
use Ubiquity\attributes\items\router\Post;
use Ubiquity\attributes\items\router\Route;
use Ubiquity\controllers\auth\AuthController;
use Ubiquity\controllers\auth\WithAuthTrait;
use Ubiquity\controllers\Controller;
use Ubiquity\security\acl\controllers\AclControllerTrait;
use Ubiquity\security\csrf\UCsrfHttp;
use Ubiquity\security\data\EncryptionManager;
use Ubiquity\utils\http\URequest;
use Ubiquity\utils\http\USession;

#[Route(path: "Main",automated: true)]
#[Resource('Main')]
class MainController extends Controller {
	use AclControllerTrait,WithAuthTrait{
     WithAuthTrait::isValid insteadof AclControllerTrait;
     AclControllerTrait::isValid as isValidAcl;
    }

    #[Permission('READ',49)]
    #[Allow('@USER','Main','READ')]
	public function index() {
		$this->loadView("MainController/index.html");
	}

    #[Route('autre')]
    #[Permission('SHOW',50)]
    public function autreAction() {
        echo 'SHOW autorisé';
    }

	public function _getRole() {
		return USession::get('activeUser','@NOBODY');
	}

    protected function getAuthController(): AuthController
    {
        return new MyAuth($this);
    }

    #[Allow('@USER')]
    #[Post('debit',name:'main.debitPost')]
    public function debitCompte(){
        if(!URequest::isCrossSite() && UCsrfHttp::isValidPost('debit.form')) {
            $numero = URequest::post('id');
            echo "le compte $numero a été débité<br>";
            $crypt=EncryptionManager::encrypt($numero);
            echo "version crypté: <pre>$crypt</pre> <br>";
            $decrypt=EncryptionManager::decryptString($numero);
            echo "version crypté: <pre>$decrypt</pre> <br>";
        }else{
            echo "<h1> Tentative d'attaque CSRF !</h1>";
        }
    }

    #[Route('load')]
    #[Allow('@USER')]
    public function loadNumCompte(){
        $crypt=USession::set('num','aa');
        echo "version cryptée : <pre>$crypt</pre> <br>";
        $decrypt=EncryptionManager::decryptString($crypt);
        echo "version crypté: <pre>$decrypt</pre> <br>";
    }

    #[Allow('@USER')]
	#[Get(path: "debitForm",name: "main.debitForm")]
	public function debitForm(){
		
		$this->loadView('MainController/debitForm.html');

	}

}
