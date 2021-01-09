<?php

namespace Plugin\CrossBorder1\Controller\Admin;

use Eccube\Controller\AbstractController;
use Plugin\CrossBorder1\Entity\Config;
use Plugin\CrossBorder1\Form\Type\Admin\ConfigDownLoadType;
use Plugin\CrossBorder1\Repository\LangContentRepository;
use Plugin\CrossBorder1\Form\Type\Admin\ConfigType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Plugin\CrossBorder1\Repository\ConfigRepository;
use Plugin\CrossBorder1\Form\Type\Admin\ConfigLangType;
use Plugin\CrossBorder1\Config\Constant\Setting;
use Plugin\CrossBorder1\Service\Controller\ConfigController\Functions;
use Plugin\CrossBorder1\Form\Type\Admin\ConfigUploadType;
use Symfony\Component\HttpFoundation\File\File;


class ConfigController extends AbstractController
{
    /**
     * @var LangContentRepository
     */
    protected $langContentRepository;

    protected $configRepository;

    private $functions;

    /**
     * ConfigController constructor.
     *
     * @param LangContentRepository $langContentRepository
     */
    public function __construct(
        LangContentRepository $langContentRepository,
        ConfigRepository $configRepository,
        Functions $functions
    )
    {
        $this->langContentRepository = $langContentRepository;
        $this->configRepository = $configRepository;
        $this->functions = $functions;
    }

    /**
     * @Route("/%eccube_admin_route%/cross_border1/config", name="cross_border1_admin_config")
     * @Template("@CrossBorder1/admin/config.twig")
     */
    public function index(Request $request)
    {
        $builder = $this->formFactory->createBuilder(ConfigLangType::class);
        $form = $builder->getForm();
        $Configs = $this->configRepository->findBy(
            [
                //
            ],
            [
                'sort_no' => 'ASC',
            ]
        );
        $data = [];
        foreach($Configs as $value){
            $data['names'][$value->getId()]['id'] = $value->getId();
            $data['names'][$value->getId()]['sort_no'] = $value->getSortNo();
            $data['names'][$value->getId()]['visible'] = $value->getVisible();
            $data['names'][$value->getId()]['name'] = $value->getName();
            $data['names'][$value->getId()]['backend_name'] = $value->getBackendName();
        }
        $choices = $this->functions->getUploadedFileChoices(Setting::YAML_UPLOAD_PATH.'/*');
        $data['uploaded_files'] = $choices;
        $builder2 = $this->formFactory->createBuilder(ConfigType::class, $data);
        $form2 = $builder2->getForm();
        $form2->handleRequest($request);
        if($form2->isValid() && $form2->isSubmitted()){
            switch($request->get('mode'))
            {
                case 'upload':
                    if(!$result = $this->functions->upload($form2, Setting::YAML_UPLOAD_PATH)){
                        $this->addError($result['error'], 'admin');
                    }
                    $this->addSuccess('アップロードが完了しました。', 'admin');
                    return $this->redirectToRoute('cross_border1_admin_config');
                    break;
                case 'download':
                    $target_file_name = $request->get('config')['uploaded_files'];
                    if(!$this->functions->fileExistsValidate($target_file_name)){
                        $this->addError('入力されたファイルが存在しませんでした。', 'admin');
                        return $this->redirectToRoute('cross_border1_admin_config');
                    }
                    $file_path = Setting::YAML_UPLOAD_PATH.'/'.$target_file_name;
                    $file = new File($file_path);
                    return $this->file($file);
                    break;
                case 'delete':
                    $target_file_name = $request->get('config')['uploaded_files'];
                    if(!$this->functions->fileExistsValidate($target_file_name)){
                        $this->addError('入力されたファイルが存在しませんでした。', 'admin');
                        return $this->redirectToRoute('cross_border1_admin_config');
                    }
                    $file_path = Setting::YAML_UPLOAD_PATH.'/'.$target_file_name;
                    if(unlink($file_path)){
                        $this->addSuccess($target_file_name . 'を削除しました。', 'admin');
                        return $this->redirectToRoute('cross_border1_admin_config');
                    }else{
                        $this->addError('システムエラーにより削除に失敗しました。', 'admin');
                        return $this->redirectToRoute('cross_border1_admin_config');
                    }

                    break;
                case 'register':
                    $input_data = $form2->getData();
                    $save_configs = [];
                    foreach($input_data['names'] as $value){
                        if(isset($value['id']) && $value['id'] > 0){
                            $Config = $this->configRepository->get($value['id']);
                        }else{
                            $Config = new Config();
                        }
                        $config_id = $this->functions->saveConfig($Config, $value);
                        $save_configs[] = $config_id;
                    }
                    if(!empty($Configs)){
                        if(!empty($save_configs)){
                            foreach($Configs as $Config){
                                if(!in_array($Config->getId(), $save_configs)){
                                    $this->configRepository->delete($Config);
                                }
                            }
                        }elseif(empty($input_data['names'])){
                            foreach($Configs as $Config){
                                $this->configRepository->delete($Config);
                            }
                        }
                        $this->entityManager->flush();
                    }

                    $this->addSuccess('登録しました。', 'admin');
                    return $this->redirectToRoute('cross_border1_admin_config');
                    break;
            }
        }

        return [
            'form' => $form->createView(),
            'form2' => $form2->createView(),
        ];
    }

    /**
     * @Route("/%eccube_admin_route%/cross_border1/config/guide", name="cross_border1_admin_config_guide")
     * @Template("@CrossBorder1/admin/config_guide.twig")
     */
    public function guide(Request $request)
    {
        return [];
    }
}
