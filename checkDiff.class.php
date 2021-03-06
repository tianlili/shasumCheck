 <?php
    $ROOT = str_replace('\\', '/', dirname(__FILE__));
    $ROOT_url = "http://10.48.30.87:8088/fis2.0_shasumCheck";
    require_once $ROOT . '/libs/smarty-3.1.5/Smarty.class.php';
    require_once $ROOT . '/File.class.php';

    $smarty = new Smarty();
    $smarty->setTemplateDir('/result');

    $config = array(
        'fis' => 'http://registry.npmjs.org/fis',
        'fis-kernel' => 'http://registry.npmjs.org/fis-kernel',
        'fis-plus' => 'http://registry.npmjs.org/fis-plus',
        'fis-command-server' => 'http://registry.npmjs.org/fis-command-server',
        'fis-command-release' => 'http://registry.npmjs.org/fis-command-release',
        'fis-command-install' => 'http://registry.npmjs.org/fis-command-install',
        'fis-spriter-csssprites' => 'http://registry.npmjs.org/fis-spriter-csssprites',
        'fis-optimizer-clean-css' => 'http://registry.npmjs.org/fis-optimizer-clean-css',
        'fis-optimizer-uglify-js' => 'http://registry.npmjs.org/fis-optimizer-uglify-js',
        'fis-postprocessor-jswrapper' => 'http://registry.npmjs.org/fis-postprocessor-jswrapper',
        'fis-packager-map' => 'http://registry.npmjs.org/fis-packager-map',
        'fis-optimizer-png-compressor' => 'http://registry.npmjs.org/fis-optimizer-png-compressor',
        'ci-test' => 'http://registry.npmjs.org/ci-test'
    );
    #two dimensional array
    $repos = array();
    $isDiff= "isDiff";
    $versions = "versions";
    $url   = "url";
    $smarty->assign('isDiff', $isDiff);
    $smarty->assign('vers', $versions);
    $smarty->assign('url', $url);

    #repo loop
    foreach ($config as $key => $u) {
        // $repos[$key]['name'] = $key;
        $data = file_get_contents( $u );
        preg_match_all("/\"dist\":{.*.tgz\"}/U", $data, $match);

        $file = $ROOT . '/repo_source/' . $key . '/shasum.json';
        if( !file_exists($file) ) {
            $repos[$key][$isDiff]  = -2;
            continue;
        }
        $shasum_local = file_get_contents($file);
        $shasum_local = json_decode($shasum_local, true);
        #string dist:{} to key=version => value=shasum
        foreach ($match[0] as $i => $dist) {
            preg_match('/(\/-\/)[a-zA-Z-]+[\d\.]+/si', $dist, $m);
            preg_match('/[\d\.]+/si', $m[0], $n);
            $v = substr($n[0], 0, strlen($n[0])-1);

            preg_match('/\"shasum\":\".*\"/siU', $dist, $g);
            preg_match('/:\"\w+/', $g[0], $h);
            $shasum = substr($h[0], 2);

            $repos[$key][$isDiff] = 0;
            $repos[$key][$url] = $ROOT_url."/result/detail_".$key.".html";
            if( isset($shasum_local[$key.'@'.$v]['shasum']) )
            {
                if( $shasum_local[$key.'@'.$v]['shasum']==$shasum ){
                    $repos[$key][$versions][$v] = 0;
                }else{
                    $repos[$key][$versions][$v] = 1;
                    $repos[$key][$isDiff]  = 1;
                }
            }
        }
    }

    $main_page = $ROOT_url."/result/checkList.html";
    $smarty->assign('main_page', $main_page);
    $smarty->assign('repos', $repos);

    foreach ($repos as $key => $repo) {
        if ($repo[$isDiff]==1 || $repo[$isDiff]==0) {
            $smarty->assign('name', $key);
            $html = $smarty->fetch($ROOT."/result/detail.tpl");
            File::write($ROOT."/result/detail_".$key.".html",$html);
        }
    }

    $html_ = $smarty->fetch($ROOT."/result/checkList.tpl");
    // echo $html;
    File::write($ROOT."/result/checkList.html",$html_);

    // //展示文件内容的不同
    // $this->DisplayDetails();

    // //不同文件的list展示
    // $this->smarty->assign('newversion',file_get_contents($this->proInfo['newoutputdir']."/fis_version.txt"));
    // $this->smarty->assign('oldversion',file_get_contents($this->proInfo['oldoutputdir']."/fis_version.txt"));
    // $this->smarty->assign('productname',$this->proInfo['name']);
    // $this->smarty->assign('difflist',$this->Diff);
    // $html = $this->smarty->fetch($smartyConfig['templatedir']."/difflist.tpl");
    // File::write($smartyConfig['templatedir'].$this->proInfo['name']."_difflist.html",$html);
    // $this->generateReport($this->Diff);
