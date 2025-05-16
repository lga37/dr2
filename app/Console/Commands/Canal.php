<?php

namespace App\Console\Commands;

use App\Models\Video;
use Illuminate\Support\Str;
use App\Models\Canal as CanalModel;
use Illuminate\Console\Command;
use HeadlessChromium\Exception\OperationTimedOut;
use HeadlessChromium\Exception\ElementNotFoundException;

class Canal extends Bot
{

    protected $signature = 'canal {canal_ids?*} {--acao=craw}';


    public function craw(array $canal_ids)
    {


        $queries = CanalModel::whereIn('id', $canal_ids)->select('cod', 'id')->get()->toArray();

        #dd($queries);
        foreach ($queries as $query) {
            $cod = $query['cod'];
            $canal_id = $query['id'];

            $url = 'https://www.youtube.com' . urldecode($cod);

            $youtube_id = $dt = $local = $links = $nome = $desc = $slug = $lang = null;

            echo "\n" . $url ."\n";

            try {

                $this->initBrowser(true);
                $page = $this->browser->createPage();

                $page->navigate($url);

                sleep(6);
                $views = $inscritos = $videos = $nome = $desc = $slug = $youtube_id = $dt = $local = null;

                $metas = $page->dom()->querySelectorAll('meta');
                foreach ($metas as $meta) {
                    if ($meta->getAttribute('itemprop') == 'identifier') {
                        $youtube_id = $meta->getAttribute('content'); #cuidado, nao e bom usar canal_id p causa das fk do laravel
                    }
                    if ($meta->getAttribute('itemprop') == 'name') {
                        $nome = $meta->getAttribute('content');
                        $slug = Str::slug($nome);
                    }
                    if ($meta->getAttribute('itemprop') == 'description') {
                        $desc = $meta->getAttribute('content');
                        $desc = limpaEspacosTabs($desc);
                    }
                }
                // <meta itemprop="identifier" content="UCsra3f6ogpXhIZbSUe2OoaA">

                $seletor_mais = '#page-header > yt-page-header-renderer > yt-page-header-view-model > div > div.page-header-view-model-wiz__page-header-headline > div > yt-description-preview-view-model > truncated-text > button > span > span';

                $page->evaluate("document.querySelector('" . $seletor_mais . "').click();");
                sleep(2);


                $res = [
                    'videos' => '<td class="style-scope ytd-about-channel-renderer">([\d\.]+?) videos<\/td>',
                    'views' => '<td class="style-scope ytd-about-channel-renderer">([\d\.]+) visualizacoes<\/td>',
                    'dt' => '>Inscreveu-se em (\d{1,2}) de (.+) de (\d{4})<\/span>',
                    'local' => '<td class="style-scope ytd-about-channel-renderer">([\w\s]+)<\/td> <\/tr> <\/tbody>',
                    'inscritos' => '<td class="style-scope ytd-about-channel-renderer">([\d,]+)\s(.+) inscritos<\/td> <\/tr>',

                ];

                $re_links = [
                    'link' => '>([\d\w\/\.]+)<\/a><\/span><\/div><\/yt-channel-external-link-view-model>',
                    'nome_link' => '>([\d\w\s]+)<\/span>',
                ];

                $seletor_modal = '#about-container';
                $modal = $page->dom()->querySelector($seletor_modal);
                if ($modal) {
                    $modal = $modal->getHtml();
                    $modal = limpaEspacosAcentuacao($modal);

                    #echo "\n\n".$modal ."\n\n";


                    foreach ($res as $key => $re) {

                        if (preg_match('/' . $re . '/', $modal, $res)) {

                            dump($key, $res);
                            if ($key == 'dt') {
                                $mes = $res[2];
                                $mes = filtraLetras($mes);
                                $mes = retornaMes($mes);
                                $$key = $res[3] . '-' . $mes . '-' . $res[1];
                            } elseif($key == 'inscritos') {
                                $$key = $res[1] . $res[2];
                            } else {
                                $$key = $res[1];
                            }
                        }
                    }
                }

                $inscritos = $inscritos ? retornaMilMilhaoBilhaoToInt($inscritos) : 0;
                $views = $views ? filtraDigitos($views) : 0;
                $videos = $videos ? filtraDigitos($videos) : 0;

                $campos = compact('views', 'inscritos', 'videos', 'nome', 'desc', 'slug', 'youtube_id', 'dt', 'local');

                #dd($campos);

                $canal = CanalModel::findOrFail($canal_id);
                $res = $canal->update($campos);
                echo "\n---------- canal numero $canal_id atualizado com " . $res ? 'sucesso' : 'erro';

            } catch (OperationTimedOut $e) {
                echo 'OperationTimedOut : ' . $e->getMessage();
            } catch (ElementNotFoundException $e) {
                echo '::::33333' . $e->getMessage();
            } catch (\Exception $e) {
                echo 'Exception : ' . $e->getMessage();
            } finally {

                #dump($ids);
                $this->closeBrowser();
                #return $ids;
            }
        }
    }


    public function handle()
    {

        $canal_ids = $this->argument('canal_ids');
        $acao = $this->option('acao');


        if (method_exists($this, $acao)) {
            #echo "method exists";
            $this->{$acao}($canal_ids);
        } else {
            echo "Este metodo nao existe";
        }


        #$this->info('Processado com sucesso!');

        return self::SUCCESS;
    }
}
