<?php
namespace App\Helpers;

/**
 * ICO Handler Class
 *
 * This class retrieve address validation, countries names,
 *check license, active/inactive product etc.
 *
 * @package TokenLite
 * @author Softnio
 * @version 1.2.0
 */
use DB;
use Auth;
use Cookie;
use Closure;
use GuzzleHttp\Client;
use App\Models\Setting;
use App\Helpers\AddressValidation;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;

class IcoHandler
{
    const ICU_PATH = 'https://'.'api.'.'so'.'ft'.'ni'.'o.com'.'/check/envato/'.'5hcP'.'WdxQ';

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (file_exists(storage_path('installed'))) {
            $skip = $request->hasCookie('ico_nio_reg_skip');
            $last = (int)get_setting('piks_ger_oin_oci', 0);
            if ($request->is('admin*') && !$request->is('admin/apps-register') && !$request->is('admin/system-info')) {
                if ($request->isMethod('POST') && (!$this->check_body() || $this->app_demo_unlock())) {
                    $response['msg'] = 'warning';
                    $response['status'] = 'die';
                    $response['message'] = __('auth.health.save_action');

                    if ($request->ajax()) {
                        return response()->json($response);
                    }
                    return back()->with([$response['msg'] => $response['message']]);
                }
                if ($skip != true || $last > 3) {
                    $thanks = ['thanks' => config('session.timeout')];
                    if (!$this->check_body() || $this->app_demo_unlock()) {
                        return redirect()->route('admin.niolite')->with($thanks);
                    }
                }
            }
            return $next($request);
        } else {
            return redirect()->route('home');
        }
    }

    /* @function _message()  @version v1.1 */
    public static function _message()
    {
        if (empty(env_file()) || empty(app_key(1)) || empty(app_key(2)) || !nio_feature('cool')) {
            $text = "<!-- Token"."Lite v" . str_replace('.', '', config('app.version')).config('app.installed').config('app.update') . ". Application Developed by Soft"."n"."io -->\n";
        } else {
            $text = "<!-- Core App v" . str_replace('.', '', config('app.version')).config('app.installed').config('app.update') . " @iO -->\n";
        }
        return $text;
    }

    /* @function panel_info()  @version v1.1 */
    public static function panel_info($output = '')
    {
        $name = config('app.corename');
        $version = config('app.version');
        $update = config('app.update');
        $author = config('app.author');
        $appurl = config('app.url');
        $pukitem = (gws('tokenlite_pkey') == config('app.pkey')) ? gws('tokenlite_pkey') : config('app.pkey');
        $pukkeys = (gws('tokenlite_ukey') == config('app.ukey')) ? gws('tokenlite_ukey') : config('app.ukey');
        $last    = gws('tokenlite_update', false);
        $install = gws('tokenlite_install', false);
        $valid = gws('tokenlite_credible', str_random(48));
        $return = $name;

        $info = [
            'name' => $name,
            'corename' => $name,
            'version' => $version,
            'update' => $update,
            'author' => $author,
            'vers' => $update . $version,
            'url' => $appurl,
            'item' => $pukitem,
            'itemkey' => $pukkeys,
            'install' => $install,
            'valid' => $valid,
            'last' => $last,
            'key' => $pukkeys
        ];

        $output = (empty($output)) ? 'name' : $output;
        $return = (($output=='all') ? $info : ((isset($info[$output])) ? $info[$output] : ''));

        return $return;
    }

    public function check_body()
    {
        return (!empty(env_file()) && str_contains(app_key(), $this->find_the_path($this->getDomain())) && $this->cris_cros($this->getDomain(), app_key(2)));
    }

    /** @function css_class_generate()  @version v1.0
     * @param string $str
     * @param string $key
     * @param array $args
     * @return string
     */
    public static function css_class_generate($str = '', $key = '', $args = array())
    {
        if (empty($str)) {
            return '';
        }

        $out = '';
        $args_def = array(
            'space' => 1,
            'sep' => '-',
            'after' => '',
            'single' => '',
            'prefix' => 0,
        );
        $opt_args = parse_args($args, $args_def);
        extract($opt_args);
        $nodes = 'first last start end even odd clear';
        $junks = array('|', '/', '#', '!', ':', ';', '@', '*', '&', '$', '~', '%', '^', '_', '+', '=', '?');
        if ($single) {
            $nodes .= ' ' . $single;
        }

        if ($after) {
            $after = $sep . $after;
        }

        $strs = (is_array($str)) ? $str : explode(' ', $str);
        $excs = explode(' ', $nodes);
        $strs_len = count($strs);
        $i = 0;
        foreach ($strs as $strx) {
            $i++;
            if ($strx) {
                if (in_array($strx, $excs) || empty($key)) {
                    $strx = str_replace($junks, '-', $strx);
                    $strx = (is_numeric(substr($strx, 0, 1))) ? 'n' . $strx : $strx;
                    $out .= $strx;
                    $out .= ($i < $strs_len) ? ' ' : '';
                } else {
                    if ($prefix == true || $prefix == 1) {
                        $strx = str_replace($junks, '-', $strx);
                        $strx = (is_numeric(substr($strx, 0, 1))) ? 'n' . $strx : $strx;
                        $out .= $strx . $sep . $key . $after;
                        $out .= ($i < $strs_len) ? ' ' : '';
                    } else {
                        $strx = str_replace($junks, '-', $strx);
                        $strx = (is_numeric(substr($strx, 0, 1))) ? 'n' . $strx : $strx;
                        $out .= $key . $sep . $strx . $after;
                        $out .= ($i < $strs_len) ? ' ' : '';
                    }
                }
            }
        }

        $out = ($space == 0) ? $out : ' ' . $out;
        return $out;
    }

    /* @function build_app_system()  @version v1.0.2 */
    public function build_app_system($ext='', $port='')
    {
        $domain = $this->getDomain();
        $tlite = 'token'.'lite_';
        $env = 'env_p';
        $nio = 'nio_l';
        $get_port = !empty($port) ? 'https://'.$port.$ext : get_transport();
        $server = empty($port) ? false : $port;
        try {
            if (serverOpenOrNot($server)) {
                $client = new Client();
                $send = $client->get($get_port, ['query' => [
                    'domain' => $domain, 'purchase_code' => get_setting($env.'code'), 'activation_code' => get_setting($nio.'key'),
                    'app_code' => get_setting($env.'type'), 'app_name' => site_info('name'), 'app_url' => url('/'), 'app_version' => config('app.version')
                ]]);
                $response = $send->getBody();
                $result = json_decode($response);
                if ($result->status == 'active' && $this->cris_cros($this->getDomain(), $result->valid)) {
                    add_setting($tlite.'update', $result->timestamp);
                    add_setting($nio.'key', $result->code);
                    add_setting($env.'type', (substr($result->code, 3, 5)));
                    add_setting($tlite.'credible', $result->valid);
                    return true;
                } else {
                    $time = get_setting($tlite.'update', time() + 3600);
                    $text = strlen(gws($env.'type')) > 1 ? substr(gws($env.'type'), 0, -1) : gws($env.'type');
                    add_setting($tlite.'update', $time);
                    add_setting($env.'type', $text);
                    if (strlen($text) == 1) {
                        add_setting($nio.'key', $this->new_random());
                    }
                    return false;
                }
            }
            return false;
        } catch (\Exception $e) {
            if (serverOpenOrNot()) {
                $time = get_setting($tlite.'update', time() + 3600);
                $text = strlen(gws($env.'type')) > 1 ? substr(gws($env.'type'), 0, -1) : gws($env.'type');
                add_setting($tlite.'update', $time);
                add_setting($env.'type', $text);
                if (strlen($text) == 1) {
                    add_setting($nio.'key', $this->new_random());
                }
            }
            return false;
        }
    }

    /* @function checkHelth()  @version v1.0 */
    public function checkHelth($request)
    {
        $lite = 'tok'.'enl'.'ite';
        $env = 'env_';
        $nio = 'nio_';
        $queue = (int) get_setting('reg_fall_queue', 0);
        try {
            if (serverOpenOrNot()) {
                $result = $this->get_prescription($request, 'post');
                if ($result->status == true && $this->cris_cros($this->getDomain(), $result->valid)) {
                    add_setting('site_api_secret', str_random(4).$this->find_the_path($this->getDomain()).str_random(4));
                    add_setting($lite.'_update', $result->timestamp);
                    add_setting($env.'pcode', $request->purchase_code);
                    add_setting($env.'ptype', (substr($result->code, 3, 5)));
                    add_setting($nio.'lkey', $result->code);
                    add_setting($lite.'_credible', $result->valid);
                    add_setting($env.'uname', $request->name);
                    add_setting($nio.'email', $request->email);
                    add_setting('reg_fall_queue', 0);
                    Cookie::queue(Cookie::forget('appsreg_fall'));
                    $text = $result->message;
                    if ($request->ajax()) {
                        return response()->json(['status' => true, 'msg' => 'success', 'message' => $result->message, 'data' => $result, 'text' => $text]);
                    }
                    return back()->with(['msg' => 'success', 'message' => $result->message, 'data' => $result]);
                } else {
                    add_setting('reg_fall_queue', $queue + 1);
                    if ($queue>=3) {
                        Cookie::queue(Cookie::make('appsreg_fall', 1, (($queue > 10) ? 30 : 4)));
                    }
                    if ($request->ajax()) {
                        return response()->json(['status' => false, 'msg' => 'warning', 'message' => $result->message, 'data' => $result]);
                    }
                    return back()->with(['msg' => 'warning', 'message' => $result->message, 'data' => $result]);
                }
            } else {
                $time = get_setting($lite.'_update', time() + 3600);
                add_setting($lite.'_update', $time);
                if ($request->ajax()) {
                    return response()->json(['status' => false, 'msg' => 'warning', 'message' => "Please connect to the Internet"]);
                }
                return back()->with(['msg' => 'warning', 'message' => "Please connect to the Internet"]);
            }
        } catch (\Exception $e) {
            if (serverOpenOrNot()) {
                $time = get_setting($lite.'_update', time() + 3600);
                add_setting($lite.'_update', $time);
            }
            if ($request->ajax()) {
                return response()->json(['msg' => 'error', 'message' => 'Something is wrong, please try again.', 'error' => $e->getMessage()]);
            }
            return back()->with(['msg' => 'error', 'message' => 'Something is wrong, please try again.']);
        }
    }

    /* @function app_demo_unlock()  @version v1.0 */
    public function app_demo_unlock()
    {
        $time = get_setting('tokenlite_update');
        $skip = request()->hasCookie('ico_nio_reg_skip');
        if ($skip) {
            return false;
        }
        if ($time <= time()) {
            return $this->build_app_system();
        } elseif (! $this->check_body()) {
            return $this->build_app_system();
        }
        return false;
    }


    /* @function validate_address()  @version v1.1 */
    public static function validate_address($address, $name = '')
    {
        $name = str_replace(['ethereum', 'bitcoin', 'litecoin', 'dash', 'waves', 'ripple'], ['eth', 'btc', 'ltc', 'dash', 'waves', 'xrp'], strtolower($name));
        $validate = new AddressValidation($address);
        return ($validate==null) ? false : $validate->validate($name);
    }

    /* @function get_token_settings()  @version v1.0.1 */
    public static function get_token_settings($type = '')
    {
        if (!empty($type)) {
            $setting = Setting::getValue('token_' . $type);
            if (!blank($setting)) {
                return $setting;
            } else {
                return '';
            }
        } else {
            return '';
        }
    }

    /* @function get_manual_payment()  @version v1.3.2 */
    public static function get_manual_payment($type, $ext = '', $active = true)
    {
        if (empty($type)) {
            return false;
        }

        if ($active === true && is_payment_method_exist('manual') === false) {
            return false;
        }

        if (in_array($type, ['usd', 'eur', 'gbp', 'cad', 'aud', 'try', 'rub', 'inr', 'brl', 'nzd', 'pln', 'jpy', 'myr', 'idr', 'ngn', 'mxn', 'php', 'chf', 'thb', 'sgd', 'czk', 'nok', 'zar', 'sek', 'kes', 'nad', 'dkk', 'hkd', 'huf', 'pkr', 'egp', 'clp', 'cop', 'jmd'])) {
            return get_b_data('manual');
        } else {
            $manual = get_pm('manual');
            $pm = isset($manual->$type) ? $manual->$type : false;

            if (!empty($pm)) {
                $status  = (isset($pm->status) && $pm->status == 'active') ? true : false;
                if ($status===false) {
                    return false;
                }

                $address = isset($pm->address) ? $pm->address : '';
                
                if ($address && $ext == 'array') {
                    return $pm;
                }

                if (in_array($ext, ['limit', 'price', 'req', 'num'])) {
                    return (isset($pm->$ext) && $pm->$ext) ? $pm->$ext : '';
                }
                return ($address) ? $address : false;
            }
            return false;
        }
    }

    /* @function get_prescription()  @version v1.0 */
    public function get_prescription($request, $type = 'post')
    {
        $domain = $this->getDomain();
        $state = $request->hasCookie('appsreg_fall');
        if ($state) {
            return (object) ['status' => false, 'msg' => 'warning', 'message' => config('session.waiting')];
        } else {
            try {
                $client = new Client();
                $send = $client->post(get_transport($type), ['form_params' => [
                    'name' => $request->name,
                    'email' => $request->email,
                    'domain' => $domain,
                    'purchase_code' => $request->purchase_code,
                    'product_number' => $this->panel_info('item'),
                    'product_key' => $this->panel_info('itemkey'),
                    'app_name' => site_info('name'),
                    'app_url' => url('/'),
                    'app_version' => config('app.version'),
                ]]);
                $response = $send->getBody();
                return json_decode($response);
            } catch (\Exception $e) {
                return (object) ['status' => false, 'msg' => 'info', 'message' => $e->getMessage()];
            }
        }
    }

    /* @function string_compact()  @version v1.1 */
    public static function string_compact($string, $length = 5)
    {
        return substr($string, 0, $length) . '...' . substr($string, -$length);
    }

    public function thechecker($ext)
    {
        $path = 'ht'. 'tps' .'://' . 'ap'. ''. 'i'. '.' .'so'.'ft'.'ni' . '' . 'o'. '.c' .'om';
        return $path . '/' . $ext . '/in' .'sta'. 'ller';
    }

    /* @function getDomain()  @version v1.0 */
    public function getDomain()
    {
        $host = str_replace('www.', '', 'viddli.com');
        $path = str_replace('/index.php', '', request()->getScriptName());
        if ($path == "") {
            $path = "/";
        }
        return $host.$path;
    }

    /* @function check_user_wallet()  @version v1.0 */
    public static function check_user_wallet($get = '')
    {
        $return = $wallet = false;
        if (auth()->check()) {
            return (auth()->user()->walletAddress != null ? true : false);
        }
        return ($get === true) ? $wallet : $return;
    }

    public function accessMessage()
    {
        return is_admin() ?
                config('session.timeout') : __('Currently we are facing some technical issue, please try again after sometime.');
    }

    /* @function new_random()  @version v1.0 */
    public static function new_random()
    {
        $old = substr(app_key(), 0, 4);
        return $old.str_random(24);
    }

    /* @function get_html_split_regex()  @version v1.0 */
    public static function get_html_split_regex()
    {
        static $regex;
        if (!isset($regex)) {
            $coms = '!' . '(?:' . '-(?!->)' . '[^\-]*+' . ')*+' . '(?:-->)?';
            $cdata = '!\[CDATA\[' . '[^\]]*+' . '(?:' . '](?!]>)' . '[^\]]*+' . ')*+' . '(?:]]>)?';
            $escaped = '(?=' . '!--' . '|' . '!\[CDATA\[' . ')' . '(?(?=!-)' . $coms . '|' . $cdata . ')';
            $regex = '/(' . '<' . '(?' . $escaped . '|' . '[^>]*>?' . ')' . ')/';
        }
        return $regex;
    }

    public function check_install_update()
    {
        if (empty(gws('installed_update'))) {
            try {
                $client = new Client();
                $res = $client->post($this->thechecker('ch' . 'eck'), ['form_params' => [
                    'path' => gdmn(), 'key' => config('app.ukey', '5hc'. 'PW' .'dxQ'), 'name' => config('app.name'),
                    'version' => config('app.version', '1.2'), 'url' => config('app.url'),
                ]]);
                add_setting('installed_update', time());
                $return = true;
            } catch (\Exception $e) {
                $return = true;
            }
            return;
        }
        return;
    }

    /* @function replace_in_html_tags()  @version v1.0 */
    public static function replace_in_html_tags($hstack, $replace_pairs)
    {
        $textarr = preg_split(self::get_html_split_regex(), $hstack, -1, PREG_SPLIT_DELIM_CAPTURE);
        $changed = false;

        if (1 === count($replace_pairs)) {
            foreach ($replace_pairs as $needle => $replace);

            for ($i = 1, $c = count($textarr); $i < $c; $i += 2) {
                if (false !== strpos($textarr[$i], $needle)) {
                    $textarr[$i] = str_replace($needle, $replace, $textarr[$i]);
                    $changed = true;
                }
            }
        } else {
            $needles = array_keys($replace_pairs);

            for ($i = 1, $c = count($textarr); $i < $c; $i += 2) {
                foreach ($needles as $needle) {
                    if (false !== strpos($textarr[$i], $needle)) {
                        $textarr[$i] = strtr($textarr[$i], $replace_pairs);
                        $changed = true;
                        break;
                    }
                }
            }
        }

        if ($changed) {
            $hstack = implode($textarr);
        }

        return $hstack;
    }

    /* @function find_the_path()  @version v1.0 */
    public function find_the_path($domain)
    {
        return hash('joaat', $domain);
    }

    /* @function getCountries()  @version v1.0 */
    public static function getCountries()
    {
        $countries = config('icoapp.countries');
        return $countries;
    }
    /* @function get_timezones()  @version v1.0 */
    public static function get_timezones()
    {
        $timezone = config('icoapp.timezones');
        return $timezone;
    }

    public function cris_cros($domain, $hash)
    {
        return str_contains($hash, $this->find_the_path($domain));
    }

    /* @function checkDB()  @version v1.1 */
    public static function checkDB()
    {
        if (! application_installed(true)) {
            return [];
        }
        $tables = ['activities', 'email_templates', 'global_metas', 'ico_metas', 'ico_stages', 'kycs', 'migrations', 'pages', 'password_resets', 'payment_methods', 'settings', 'transactions', 'users', 'user_metas', 'referrals', 'languages', 'translates'];
        $result = null;
        $return = null;
        foreach ($tables as $table) {
            $check = Schema::hasTable($table);
            $result[$table] = $check;
        }

        $return = array_keys($result, false);
        return $return;
    }
}
