<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

// Guest_Controller is used for all views that are accessable by the Guest
class Security_Controller extends CI_Controller
{
    const USER_LEVEL_BOSS = 1;
    const USER_LEVEL_MEMBER = 2;
    protected $required_access_level = null;
    protected $user_level = null;
    protected $user_level_types = array(
        'admin' => self::USER_LEVEL_BOSS,
        'members' => self::USER_LEVEL_MEMBER
    );

    public function __construct($accessLevel = self::USER_LEVEL_BOSS, $logged_in = TRUE)
    {
        parent::__construct();

        $this->_setMinimumUserLevel($accessLevel, $logged_in);

        //alerts
        if ($this->session->flashdata('error'))

        //load language
        $this->_loadLang();
    }

    //lang can switch by providing url like:
    //http://hk.jousun.com/en/aisles/{name of aisle} => http://hk.jousun.com/aisles/{name of aisle}
    protected function _loadLang()
    {
        //set language session if a parameter is passed in the URL
        $set_lang = $this->config->item('default_language');
            
        if (!empty($set_lang))
        {
            //page language
            if ($set_lang && is_dir(APPPATH . 'language/' . $set_lang))
            {
                $this->lang->load('ion_auth', $set_lang);
            } else
            {
                $this->lang->load('ion_auth', $this->config->item('language'));
            }
        } else
        {
            $this->lang->load('ion_auth', $this->config->item('language'));
        }
    }

    /**
     * Set the access level required for the controller or action
     * This function will reroute if the access is not met
     *
     * @param int $level
     */
    protected function _setMinimumUserLevel($level, $logged_in = TRUE)
    {
        //Cannot require a company level without requiring login
        if ($level && !$logged_in)
        {
            throw new Exception('Invalid authentication parameters');
        }
        $this->required_access_level = $level;
        $this->require_logged_in = $logged_in;
        $this->_check_auth();
    }

    protected function _check_auth()
    {
        if (!defined('PHPUNIT_TEST'))
        {
            if ($this->require_logged_in)
            {
                $expired_message = 'Oops! Your session has expired. To continue, please log in again.';
                //CHECK IF LOGGED IN
                if (!$this->ion_auth->logged_in())
                {
                    //set layout
                    $this->layout->is_logged_in = FALSE;

                    // If it is requested ajax return json 
                    // instead redirecting to login page
                    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
                    {
                        $response['message'] = $expired_message;
                        $response['relogin'] = TRUE;
                        exit(json_encode($response));
                    } else
                    {
                        $this->session->set_flashdata('lasturl', "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
                        $this->session->set_flashdata('message', $expired_message);
                        redirect('auth/login');
                    }
                } else {
                    //set layout
                    $this->layout->is_logged_in = TRUE;
                }

                //CHECK IF ADMIN
                if (!$this->ion_auth->is_admin()) {
                    $this->layout->is_admin = FALSE;
                } else {
                    $this->layout->is_admin = TRUE;
                }

                $this->user_level = $this->_getUserLevel();

                $this->layout->user_level = $this->user_level;
                $this->layout->required_access_level = $this->required_access_level;

                $this->_checkSession();
            }

            if (!empty($this->required_access_level) &&
                $this->user_level > $this->required_access_level)
            {
                redirect('auth/forbidden');
            }
        }
    }

    protected function _getUserLevel()
    {
        $user_level = 9999;

        if ($this->ion_auth->in_group('admin'))
        {
            $user_level = $this->user_level_types['admin'];
        } else if ($this->ion_auth->in_group('members'))
        {
            $user_level = $this->user_level_types['members'];
        }

        return $user_level;
    }

    /**
     * Keep our Session vars in check
     */
    protected function _checkSession()
    {
        if ($this->ion_auth->logged_in())
        {
            
        }
    }

    

    public function _output($content = '')
    {
        if ($this->is_ajax)
        {
            $this->data['content'] = $content;
            $this->_outputNoLayout($this->data);
        } else
        {
            $this->_outputLayout($content);
        }
    }

    public function _outputLayout($data)
    {
        if ($data)
        {
            $this->layout->get_layout()->content($data);
        }
        $content = $this->layout->get_layout()->render();

        echo $content;
    }

    protected function _outputNoLayout($data)
    {
        switch ($this->return_head) {
            case 'html':
                echo $data['content'];
                break;
            case 'json':
                if (!$this->input->is_cli_request()) {
                    header('Content-Type: application/json', TRUE);
                }
                echo $data['content'];
                break;
            case 'javascript':
                header('Content-Type: application/javascript', TRUE);
                echo $data['content'];
                break;
            default:
                break;
        }
    }

    /**
     * callback validators for form validation
     *
     * checks to make sure the url contains a valid url.  Must include http:// or https://
     * @param string $str
     * @return boolean
     */
    public function url_check($str)
    {
        $regex = '/(((http|ftp|https):\/{2})+(([0-9a-z_-]+\.)+(aero|asia|biz|cat|com|coop|edu|gov|info|int|jobs|mil|mobi|museum|name|net|org|pro|tel|travel|ac|ad|ae|af|ag|ai|al|am|an|ao|aq|ar|as|at|au|aw|ax|az|ba|bb|bd|be|bf|bg|bh|bi|bj|bm|bn|bo|br|bs|bt|bv|bw|by|bz|ca|cc|cd|cf|cg|ch|ci|ck|cl|cm|cn|co|cr|cu|cv|cx|cy|cz|cz|de|dj|dk|dm|do|dz|ec|ee|eg|er|es|et|eu|fi|fj|fk|fm|fo|fr|ga|gb|gd|ge|gf|gg|gh|gi|gl|gm|gn|gp|gq|gr|gs|gt|gu|gw|gy|hk|hm|hn|hr|ht|hu|id|ie|il|im|in|io|iq|ir|is|it|je|jm|jo|jp|ke|kg|kh|ki|km|kn|kp|kr|kw|ky|kz|la|lb|lc|li|lk|lr|ls|lt|lu|lv|ly|ma|mc|md|me|mg|mh|mk|ml|mn|mn|mo|mp|mr|ms|mt|mu|mv|mw|mx|my|mz|na|nc|ne|nf|ng|ni|nl|no|np|nr|nu|nz|nom|pa|pe|pf|pg|ph|pk|pl|pm|pn|pr|ps|pt|pw|py|qa|re|ra|rs|ru|rw|sa|sb|sc|sd|se|sg|sh|si|sj|sj|sk|sl|sm|sn|so|sr|st|su|sv|sy|sz|tc|td|tf|tg|th|tj|tk|tl|tm|tn|to|tp|tr|tt|tv|tw|tz|ua|ug|uk|us|uy|uz|va|vc|ve|vg|vi|vn|vu|wf|ws|ye|yt|yu|za|zm|zw|arpa)(:[0-9]+)?((\/([~0-9a-zA-Z\#\+\%@\.\/_-]+))?(\?[0-9a-zA-Z\+\%@\/&\[\];=_-]+)?)?))\b/imuS';
        if (!preg_match($regex, $str))
        {
            $this->form_validation->set_message('url_check', 'The %s field must be valid URL and contain "http://".  For example, http://www.google.com.');
            return FALSE;
        } else
        {
            return TRUE;
        }
    }

    /**
     * checks to make sure only alpha numeric and spaces allowed.
     * @param type $str
     * @return boolean
     */
    public function alpha_numeric_space($str)
    {
        if (!preg_match('/^[a-z0-9\s]+$/i', $str))
        {
            $this->form_validation->set_message('alpha_numeric_space', 'The %s field may only contain alpha-numeric and space characters.');
            return FALSE;
        } else
        {
            return TRUE;
        }
    }

    /**
     *
     * @param type $subdir - subdirectory of main upload directory to upload to
     * @param mixed $filename - string - name of file form field.
     *                          - array - array of names for file form field
     * @return type
     */
    protected function do_upload($subdir = '', $filename = 'fileinputname')
    {
        $this->_setMinimumUserLevel(self::USER_LEVEL_BOSS, TRUE);

        $config['upload_path'] = $this->config->item('upload_path') . (($subdir == '') ? '' : $subdir);
        $config['allowed_types'] = 'gif|jpg|png|doc|xls|docx|xlsx|ppt|pptx|pdf';
        $config['max_size'] = '20000';
        $config['max_width'] = '0';
        $config['max_height'] = '0';
        $config['remove_spaces'] = TRUE;
        $config['encrypt_name'] = TRUE;

        $this->load->library('upload', $config);

        if (!$this->upload->do_upload($filename))
        {
            return array('error' => $this->upload->display_errors());
        } else
        {
            return array('upload_data' => $this->upload->data());
        }
    }

    public function do_delete($subdir = '', $filenames)
    {
        //get image dir
        $_sd = ($subdir == '') ? '' : $subdir . '/';
        $file_dir = $this->config->item('upload_path') . $_sd;

        if (is_array($filenames))
        {
            foreach ($filenames as $name)
            {
                if (file_exists($file_dir . $name))
                {
                    unlink($file_dir . $name);
                }
            }
        } else
        {
            if (file_exists($file_dir . $filenames))
            {
                unlink($file_dir . $filenames);
            }
        }

        return TRUE;
    }

    /**
     *
     * @param type $filename
     * @param type $method - download | stream
     */
    public function read($filename = null, $altname = null, $method = 'download', $subdir = '')
    {
        $this->is_ajax = TRUE;

        if (empty($filename))
        {
            redirect('auth/forbidden');
        }

        $this->load->helper('file');
        $this->load->helper('download');

        $file_path = $this->config->item('upload_path') . (($subdir == '') ? '' : $subdir) . $filename;
        $file_info = get_file_info($file_path);
        $mime = get_mime_by_extension($file_path);
        $ext = pathinfo($file_path, PATHINFO_EXTENSION);
        $file = file_get_contents($file_path);

        if ($method == 'download')
        {
            $_f = (empty($altname)) ? $filename : $altname . '.' . $ext;
            force_download($_f, $file);
        } else
        {
            echo $file;
        }
    }

    protected function sortAisles($aisles) 
    {
        $aisle_datas = $aisles->results;
        $_aisles = '';
        foreach ($aisle_datas as $aisle_data) {
            if ($aisle_data->slug == 'gift-shop') {
                array_unshift($_aisles, $aisle_data);
            } else {
                $_aisles[] = $aisle_data;
            }
        }
        $aisles->results = $_aisles;
        return $aisles;
    }
}
