<?php

namespace Plugin\CrossBorder1\Service\Controller\ConfigController;

use Plugin\CrossBorder1\Config\Constant\Setting;
use Plugin\CrossBorder1\Entity\Config;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\MimeType\FileinfoMimeTypeGuesser;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

class Functions
{
    private $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager
    )
    {
        $this->entityManager = $entityManager;
    }

    public function saveConfig(Config $Config, $value)
    {
        $Config->setName($value['name']);
        if($value['visible'] > 0){
            $Config->setVisible(true);
        }else{
            $Config->setVisible(false);
        }
        $Config->setSortNo($value['sort_no']);
        $Config->setBackendName($value['backend_name']);
        $this->entityManager->persist($Config);
        $this->entityManager->flush();
        return $Config->getId();
    }

    public function fileNameValidate($file_name)
    {
        if(!preg_match('/(messages|validators)\.[a-z-]+\.yaml/u', $file_name)){
            return false;
        }else{
            return true;
        }
    }

    public function fileExistsValidate($file_name)
    {
        $choices = $this->getUploadedFileChoices(Setting::YAML_UPLOAD_PATH.'/*');
        if(preg_grep('{'.$file_name.'}', $choices)){
            return true;
        }else{
            return false;
        }
    }

    public function getUploadedFileChoices($path)
    {
        $uploaded_files = glob($path);
        $choices = [];
        foreach($uploaded_files as $file_path){
            $uploaded_file_name = preg_replace('/.+\/(.+)\.(.+)\.yaml$/u', '$1.$2.yaml', $file_path);
            $choices[$uploaded_file_name] = $uploaded_file_name;
        }
        return $choices;
    }

    public function upload($form, $path)
    {
        $result = [];
        /**@var UploadedFile $file*/
        $file = $form['file']->getData();
        $file_name = $file->getClientOriginalName();
        if(!$this->fileNameValidate($file_name)){
            $result['error'] = 'ファイル名が定まった形式と違います。ファイル名を確認し、再度アップロードしてください。';
            return $result;
        }
        if(!$file->move($path, $file_name)){
            $result['error'] = 'システムエラーによりファイルのアップロードに失敗しました。';
            return $result;
        }
        return true;
    }
}
