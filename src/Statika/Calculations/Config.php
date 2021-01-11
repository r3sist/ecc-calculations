<?php declare(strict_types = 1);
/**
 * User settings and system configurator of Statika framework
 * (c) Bence VÁNKOS | https://structure.hu | https://github.com/r3sist/ecc-calculations
 */

namespace Statika\Calculations;

use Base;
use Psr\Log\LoggerInterface;
use Statika\Calculation\CalculationMapper;
use User\AuthHelper;
use User\UserdataMapper;
use Statika\EurocodeInterface;
use User\UserDTO;

Class Config
{
    public const FIRMS = [
        'CÉH' => 'CEH',
        'Structure' => 'Structure',
        'Roni' => 'Roni',
    ];

    private LoggerInterface $logger;
    private UserdataMapper $userMapper;
    private CalculationMapper $calculationMapper;
    private Base $f3;

    public function __construct(Base $f3, LoggerInterface $logger, UserdataMapper $userMapper, CalculationMapper $calculationMapper)
    {
        $this->f3 = $f3;
        $this->logger = $logger;
        $this->userMapper = $userMapper;
        $this->calculationMapper = $calculationMapper;

        $this->userMapper->loadByUid($this->f3->get('uid'));
    }

    /**
     * @param Ec $ec
     * @throws \resist\Auth3\Exception\InvalidUserException
     */
    public function calc(EurocodeInterface $ec): void
    {
        /** @var UserDTO $user */
        $user = $this->f3->get('user');

        if ($user->username === 'CÉH') {
            $ec->danger('Ez a CÉH közös fiók, a módosítások mindenkinél számítanak!');
        }

        $ec->h1('Sablonok', 'MS Word export');

        $ec->lst('template', [$user->firm => self::FIRMS[$user->firm], 'Structure' => 'Structure'], ['', 'Sablon'], $user->template);

        if ($this->userMapper) {
            $this->userMapper->template = $ec->template;
            if (in_array($ec->template, self::FIRMS)){
                $this->userMapper->save();
            }
        }


        $ec->h1('Képletek kezelése');
        $ec->boo('nativeMath', ['', 'Szerveroldali ASCIIMath konvertálás MathML formátumba'], $user->is_native_mathml, 'Csak Firefox alatt! MathJax helyett szerverordali képlet renderelés. Rondább, de gyorsabb és ugrálás nélküli megjelenítés.');
        if ($this->userMapper) {
            $this->userMapper->is_native_mathml = $ec->nativeMath;
            $this->userMapper->save();
        }
        $ec->txt('', 'A módosítások aktiválásához a teljes oldal újratöltése szükséges.');

        if (AuthHelper::isAdmin()) {
            $ec->h1('Admin');
            $ec->boo('doUpdate', ['', 'Számítás meta adatok szerkesztése/mentése'], false, '');
            if ($ec->doUpdate) {
                $calculationMappers = $this->calculationMapper->find([], ['order'=>'name']);
                $this->logger->info('ecc edit', ['Edited calcs meta']);
                foreach ($calculationMappers as $mapper) {
                    $ec->region0('admin'.$mapper->name, $mapper->name);
                        $ec->input($mapper->name.'_title', ['', 'title'], $mapper->title, '', '');
                        $ec->input($mapper->name.'_subtitle', ['', 'subtitle'], $mapper->subtitle, '', '');
                        $ec->input($mapper->name.'_description', ['', 'description'], $mapper->description, '', '');
                        $ec->lst($mapper->name.'_type_id', ['S' => 'S', 'L' => 'L', 'G' => 'G', 'C' => 'C'], ['', 'group'], $mapper->type_id, '');
                        $ec->boo($mapper->name.'_is_experimental', ['', 'experimental'], (bool)$mapper->is_experimental, '');
                        $ec->boo($mapper->name.'_is_hidden', ['', 'hidden'], (bool)$mapper->is_hidden, '');
                        $ec->boo($mapper->name.'_is_private', ['', 'private'], (bool)$mapper->is_private, '');
                        $ec->boo($mapper->name.'_is_secondary', ['', 'secondary'], (bool)$mapper->is_secondary, '');

                        $mapper->title = $ec->get($mapper->name.'_title');
                        $mapper->subtitle = $ec->get($mapper->name.'_subtitle');
                        $mapper->description = $ec->get($mapper->name.'_description');
                        $mapper->type_id = $ec->get($mapper->name.'_type_id');
                        $mapper->is_experimental = $ec->get($mapper->name.'_is_experimental');
                        $mapper->is_hidden = $ec->get($mapper->name.'_is_hidden');
                        $mapper->is_private = $ec->get($mapper->name.'_is_private');
                        $mapper->is_secondary = $ec->get($mapper->name.'_is_secondary');
                        $mapper->update();

                    $ec->region1();
                }
            }

//            $ec->h1('Új számítás hozzáadása');
//            $ec->input('cnameNew', ['', 'Osztály azonosító'], '', '', 'ecc-calculation osztály azonosító (URL azonosító lesz)', 'alpha');
//            $ec->txt('Composer autoloader frissítése szükséges!');
//            if ($ec->cnameNew) {
//                $this->calculationMapper->reset();
//                $this->calculationMapper->cname = ucfirst($ec->get('_cnameNew'));
//                $this->calculationMapper->save();
//
//                $ec->toast('Új osztály beszúrva', 'success', '');
//                $ec->html('<script>$("#_cname").val("");</script>');
//            }
        }
    }
}
