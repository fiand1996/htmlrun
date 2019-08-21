<?php
/**
 * Session Core
 *
 * @date 07 June 2019
 * @version 1.0
 * @copyright Fiand T <fiand96@yahoo.com>
 * @author Fiand T (https://github.com/fiand1996)
 */
class Session 
{

    /**
	 * Userdata (fetch)
	 *
	 * Legacy CI_Session compatibility method
	 *
	 * @param	string	$key	Session data key
	 * @return	mixed	Session data value or NULL if not found
	 */
	public static function get($key = NULL)
	{
		if (isset($key))
		{
			return isset($_SESSION[$key]) ? $_SESSION[$key] : NULL;
		}
		elseif (empty($_SESSION))
		{
			return array();
		}

		return $_SESSION;
    }
    
    /**
     * Set userdata
     *
     * Legacy CI_Session compatibility method
     *
     * @param    mixed    $data    Session data key or an associative array
     * @param    mixed    $value    Value to store
     * @return    void
     */
    public static function set($data, $value = NULL)
	{
		if (is_array($data))
		{
			foreach ($data as $key => &$value)
			{
				$_SESSION[$key] = $value;
			}

			return;
		}

		$_SESSION[$data] = $value;
    }
    
    /**
	 * Unset userdata
	 *
	 * Legacy CI_Session compatibility method
	 *
	 * @param	mixed	$key	Session data key(s)
	 * @return	void
	 */
	public static function unset($key)
	{
		if (is_array($key))
		{
			foreach ($key as $k)
			{
				unset($_SESSION[$k]);
			}

			return;
		}

		unset($_SESSION[$key]);
    }
    
    /**
	 * Session destroy
	 *
	 * Legacy CI_Session compatibility method
	 *
	 * @return	void
	 */
	public static function destroy()
	{
		session_destroy();
	}
}
