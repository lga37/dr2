<?php

namespace App\Console\Commands;

use DateTime;
use App\Models\Canal;
use App\Models\Video;
use App\Models\Arxiv as ArxivModel;
use Illuminate\Support\Str;
use Illuminate\Console\Command;
use HeadlessChromium\Exception\OperationTimedOut;
use HeadlessChromium\Exception\ElementNotFoundException;

class Arxiv extends Bot
{

    protected $signature = 'arxiv {canal_ids?*} {--acao=craw}';


    public function craw(array $canal_ids)
    {
        $queries = Canal::whereIn('id', $canal_ids)->select('id', 'youtube_id')->get()->toArray();

        #dd($queries);
        foreach ($queries as $query) {
            $canal_id = $query['id'];
            $youtube_id = $query['youtube_id'];
            ######### api
            $url = "https://www.youtube.com/channel/$youtube_id";
            echo $url . "\n\n";

            $site = "https://archive.org/wayback/available?url=$url";
            $out = file_get_contents($site);
            $res = json_decode($out, true);
            $tot = 0;
            if (isset($res['archived_snapshots']['closest']['available']) && $res['archived_snapshots']['closest']['available'] == 'true') {
                $snaps = "https://web.archive.org/cdx/search/cdx?url=$url";
                $txt = file_get_contents($snaps);

                echo "\n\n$txt\n\n";
                $re = '\s([\d]{12,})\s(.+?)\s';
                $canal = Canal::find($canal_id);
                if (preg_match_all('/' . $re . '/', $txt, $res)) {
                    foreach ($res[1] as $k => $ts) {
                        if (!ArxivModel::where('canal_id', $canal_id)->where('ts', $ts)->exists()) {
                            ArxivModel::create(compact('canal_id', 'ts'));
                            $tot++;
                        }
                    }
                } else {
                    echo "nao parseou";
                }


            } else {
                dump($res);
                echo "\nATENCAO::::::::::::::::::::::::: nao tem arxiv para ele :::::::::::::::::::::::::: $url";
            }


        }
        session()->flash('status', $tot . ' arxivs adicionados');


    }


    public function process($id)
    {
        ######## craw
        // $canal->fresh();
        $canal = Canal::find($id);
        $youtube_id = $canal->first()->youtube_id;
        $url = "https://www.youtube.com/channel/$youtube_id";

        $arxivs = ArxivModel::where('canal_id', $id)->where('parsed', 0)->select('id', 'ts')->get()->toArray();

        #checar o lang do html se nao for pt en 

        foreach ($arxivs as $arx) {
            $arxiv_id = $arx['id'];
            $ts = filtraDigitos($arx['ts']);
            $url_final = "http://web.archive.org/web/$ts/$url";
            echo $url_final . "\n\n";

            try {
                $this->initBrowser(true);
                $page = $this->browser->createPage();
                $page->navigate($url_final);

                sleep(20);

                // <span class="yt-core-attributed-string yt-content-metadata-view-model-wiz__metadata-text yt-core-attributed-string--white-space-pre-wrap yt-core-attributed-string--link-inherit-color" dir="auto" role="text">677K subscribers</span>

               
                //<yt-formatted-string id="subscriber-count" class="style-scope ytd-c4-tabbed-header-renderer" aria-label="195K subscribers">195K subscribers</yt-formatted-string>
               

                #ru-RU es
                if($elem = $page->dom()->querySelector("html")){
                    if($lang = $elem->getAttribute('lang')){
                        echo "\n--------- $lang";
                        if($lang){
                            $lang = substr($lang,0,2);
                            if(in_array($lang,['ru'])){
                                echo "\n--------- idioma ----------- Site em $lang \n\n";
                                continue;
                            }
                        }
                    }
                }

                $seletor1 = 'span.yt-subscription-button-subscriber-count-branded-horizontal.yt-uix-tooltip';


                $seletor2 = 'span.yt-subscription-button-subscriber-count-branded-horizontal.subscribed.yt-uix-tooltip'; #aria-label

                $seletor3 = 'span.yt-core-attributed-string.yt-content-metadata-view-model-wiz__metadata-text yt-core-attributed-string--white-space-pre-wrap.yt-core-attributed-string--link-inherit-color';

                $seletor4 = 'span.yt-core-attributed-string.yt-content-metadata-view-model-wiz__metadata-text yt-core-attributed-string--white-space-pre-wrap.yt-core-attributed-string--link-inherit-color > span';


                $seletor5 = '#subscriber-count.style-scope.ytd-c4-tabbed-header-renderer'; #esse aqui e pelo aria-label

                $seletor6 = 'span.yt-subscription-button-subscriber-count-branded-horizontal.subscribed'; #so com gettext



                if ($elem = $page->dom()->querySelector($seletor1)) {
                    #echo $elem->getHTML();
                    $subscribers = $elem->getAttribute('title');
                    echo "subs::".$subscribers;
                    $subscribers = retornaMilMilhaoBilhaoToInt($subscribers);
                    echo "\n if1 $subscribers \n";
                    if($subscribers > 0){
                        $parsed=1;
                        ArxivModel::where('id', $arxiv_id)->update(compact('subscribers', 'parsed'));
                    }

                } elseif ($elem = $page->dom()->querySelector($seletor2)) {
                    #echo $elem->getHTML();
                    $subscribers = $elem->getAttribute('title');
                    echo "subs::".$subscribers;
                    $subscribers = retornaMilMilhaoBilhaoToInt($subscribers);
                    echo "\n if2 $subscribers \n";

                    if($subscribers > 0){
                        $parsed=1;
                        ArxivModel::where('id', $arxiv_id)->update(compact('subscribers', 'parsed'));
                    }


                } elseif ($elem = $page->dom()->querySelector($seletor3)) {
                    #echo $elem->getHTML();
                    $subscribers = $elem->getText();
                    echo "subs::".$subscribers;
                    $subscribers = retornaMilMilhaoBilhaoToInt($subscribers);
                    echo "\n if3 $subscribers \n";

                    if($subscribers > 0){
                        $parsed=1;
                        ArxivModel::where('id', $arxiv_id)->update(compact('subscribers', 'parsed'));
                    }


                } elseif ($elem = $page->dom()->querySelector($seletor4)) {
                    #echo $elem->getHTML();
                    $subscribers = $elem->getText();
                    echo "subs::".$subscribers;
                    $subscribers = retornaMilMilhaoBilhaoToInt($subscribers);
                    echo "\n if4 $subscribers \n";
                    if($subscribers > 0){
                        $parsed=1;
                        ArxivModel::where('id', $arxiv_id)->update(compact('subscribers', 'parsed'));
                    }

                } elseif ($elem = $page->dom()->querySelector($seletor5)) {
                    $subscribers = $elem->getAttribute('aria-label');
                    $subscribers = retornaMilMilhaoBilhaoToInt($subscribers);
                    echo "\n if5 $subscribers \n";

                    if($subscribers > 0){
                        $parsed=1;
                        ArxivModel::where('id', $arxiv_id)->update(compact('subscribers', 'parsed'));
                    }
                
                } elseif ($elem = $page->dom()->querySelector($seletor6)) {
                    $subscribers = $elem->getText();
                    $subscribers = retornaMilMilhaoBilhaoToInt($subscribers);
                    echo "\n if6 $subscribers \n";

                    if($subscribers > 0){
                        $parsed=1;
                        ArxivModel::where('id', $arxiv_id)->update(compact('subscribers', 'parsed'));
                    }


                } else {

                    #faz via regex no wget
                    $re = '"subscriberCountText":{"runs":\[{"text":"(.+?) subscribers"}';

                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url_final);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    $txt = curl_exec($ch);
                    curl_close($ch);

                    $res = [
                        '"subscriberCountText":{"runs":\[{"text":"(.+?) subscribers"}',
                        '{"text":{"content":"([\d\.KkmM]+?) subscribers"}}',
                    ];

                    foreach ($res as $key=>$re) {
                        if (preg_match('/' . $re . '/', $txt, $res)) {
                            #dd($res[1]);
                            $subscribers = $res[1];
                            $subscribers = retornaMilMilhaoBilhaoToInt($subscribers); #19.5K subscribers
                            #echo $txt;
                            echo "\n\n regex $subscribers chave $key \n";
                            if($subscribers > 0){
                                $parsed=1;
                                ArxivModel::where('id', $arxiv_id)->update(compact('subscribers', 'parsed'));
                            }
                            break;
                        }
                    }

                    #echo "\nNao acho o seletor (1 a 3) ----------------------- Tbm nao deu regex.";
                }


                #echo $elem->getHTML();


            } catch (OperationTimedOut $e) {
                echo 'OperationTimedOut : ' . $e->getMessage();
            } catch (ElementNotFoundException $e) {
                echo '::::33333' . $e->getMessage();
            } catch (\Exception $e) {
                echo 'Exception : ' . $e->getMessage();
            } finally {
                $this->closeBrowser();
            }
        }
    }


    public function handle()
    {

        $canal_ids = $this->argument('canal_ids');
        $acao = $this->option('acao');

        if (method_exists($this, $acao)) {
            $this->{$acao}($canal_ids);
        } else {
            echo "Este metodo nao existe";
        }

        #$this->info('Processado com sucesso!');
        return self::SUCCESS;
    }
}
