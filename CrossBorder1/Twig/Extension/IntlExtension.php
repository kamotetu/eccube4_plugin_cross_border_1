<?php

namespace Plugin\CrossBorder1\Twig\Extension;

use Twig\Environment;
use Eccube\Common\EccubeConfig;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Symfony\Component\HttpFoundation\RequestStack;

class IntlExtension extends AbstractExtension
{
    private $eccubeConfig;

    private $request;

    public function __construct(
        EccubeConfig $eccubeConfig,
        RequestStack $request
    )
    {
        $this->eccubeConfig = $eccubeConfig;
        $this->request = $request;
    }

    public function getFilters()
    {
        return [
            new TwigFilter('date_min', [$this, 'getAdminDateMin'], ['needs_environment' => true]),
            new TwigFilter('date_sec', [$this, 'getAdminDateSec'], ['needs_environment' => true]),
            new TwigFilter('date_day_with_weekday', [$this, 'date_day_with_weekday'], ['needs_environment' => true]),
        ];
    }

    public function getAdminDateMin(Environment $env, $date)
    {
        if (!$date) {
            return '';
        }
        $admin_route = $this->eccubeConfig->get('eccube_admin_route');
        if(strpos($this->request->getCurrentRequest()->getUri(), $admin_route) !== false){
            return $this->setDefaultLocale($env, $date, 'short');
        }else{
            return \twig_localized_date_filter($env, $date, 'medium', 'short');
        }
    }

    public function getAdminDateSec(Environment $env, $date)
    {
        if(!$date){
            return '';
        }

        $admin_route = $this->eccubeConfig->get('eccube_admin_route');
        if(strpos($this->request->getCurrentRequest()->getUri(), $admin_route) !== false){
            return $this->setDefaultLocale($env, $date, 'medium');
        }else{
            return \twig_localized_date_filter($env, $date, 'medium', 'medium');
        }
    }

    public function setDefaultLocale(Environment $env, $date, $type)
    {
        $locale = $this->request->getCurrentRequest()->getLocale();
        $this->request->getCurrentRequest()->setLocale($this->eccubeConfig->get('locale'));
        $value = \twig_localized_date_filter($env, $date, 'medium', $type);
        $this->request->getCurrentRequest()->setLocale($locale);
        return $value;
    }

    public function date_day_with_weekday(Environment $env, $date)
    {
        if (!$date) {
            return '';
        }

        $date_day = \twig_localized_date_filter($env, $date, 'medium', 'none');

        if($this->request->getCurrentRequest()->getLocale() !== 'ja'){
            return $date_day;
        }
        $dateFormatter = \IntlDateFormatter::create(
            'ja_JP@calendar=japanese',
            \IntlDateFormatter::FULL,
            \IntlDateFormatter::FULL,
            'Asia/Tokyo',
            \IntlDateFormatter::TRADITIONAL,
            'E'
        );
        return $date_day.'('.$dateFormatter->format($date).')';



    }
}
