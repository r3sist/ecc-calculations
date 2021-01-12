<?php declare(strict_types = 1);
/**
 *  - Calculation class for Statika framework
 * (c) Bence VÁNKOS | https://structure.hu | https://github.com/r3sist/ecc-calculations
 */

namespace Statika\Calculations;

use \H3;
use Statika\Ec;
use Statika\EurocodeInterface;

Class IdeaLoadConversion
{
    /**
     * @param Ec $ec
     */
    public function calc(EurocodeInterface $ec): void
    {
        $ec->note('Két különböző szoftverből származó IDEA modell közti exportált/imortált teher konverió.');

        $ec->html(<<<EOS
            <script>
                function onFileLoadAlt(elementId, event) {
                    document.getElementById(elementId).value = event.target.result;
                }
            </script>
            EOS);
        $ec->html('<input type="file" data-script="on change call onChooseFile(event, onFileLoadAlt.bind(this, \'uploader_content\'))"><input type="hidden" name="uploader_content" id="uploader_content" class="" value="'.$_POST['uploader_content'].'">');

        if($_POST['uploader_content']) {
            $csv = $_POST['uploader_content'];
            $ec->region0('csv', 'Nyers CSV');
            $ec->pre($csv);
            $ec->region1();
        }

        if ($csv) {
            $ec->numeric('beam_number', ['', 'Elemek száma'], 6);

            $lines = str_getcsv($csv, PHP_EOL);

            $lineArray = [];
            foreach($lines as $line) {
                $lineArray[] = str_getcsv($line, "\t");
            }

            // Remove column header
            array_shift($lineArray);

            // Get export order
            $mappingDefault = [];
            $originalOrderAsList = '';
            for ($i = 0; $i < $ec->beam_number; $i++) {
                $mappingDefault[] = [$i, $lineArray[$i][1], '', $i];
                $originalOrderAsList .= $i.' ';
            }

            $mappingFields = [
                ['name' => 'export_order', 'title' => 'ID', 'type' => 'input'],
                ['name' => 'axis', 'title' => 'Axis rúd név', 'type' => 'input'],
                ['name' => 'tekla', 'title' => 'Tekla rúd név', 'type' => 'input'],
                ['name' => 'import_order', 'title' => 'Importálás új sorrendje', 'type' => 'input'],
            ];
            $ec->txt('Exportált CSV rúdsorrend:');
            $ec->bulk('mapping', $mappingFields, $mappingDefault);

            $ec->numericArrayInput('order', ['', 'Új rúd sorrend importáláshoz'], $originalOrderAsList);

            // Keep only forces
            foreach($lineArray as &$line) {
//                array_shift($line);
//                array_shift($line);
//                array_shift($line);
                array_pop($line);
            }

            $blockArray = array_chunk($lineArray, 6, false);

            d($blockArray);
            $newBlockArray = [];
            foreach ($blockArray as $loadCombinationKey => $loadCombinationData) {
                foreach ($loadCombinationData as $beamKey => $beamData) {
                    $newBlockArray[$loadCombinationKey][(int)$ec->mapping[$beamKey]['import_order']] = $beamData;
                }
            }
            d($newBlockArray);
            $ec->pre(print_r($newBlockArray, true));


        }

    }


}
