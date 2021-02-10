<?php
class UserRequest extends Request
{
    private $user; // the User model

    function __construct() {
        parent::__construct();

        $this->secureJwtHeader();

        $this->user = new User( $this->decoded->data->user );

        if ( false === $this->user->exist() ) {
            app_false_response( 'Invalid user', 401);
        }
    }
    
    /**
     * Get the user entry
     */
    public function getInfo() : void
    {
        $user_info = $this->user->get();
        if ( 1 <= count( $user_info ) ) {
            app_true_response( 'Fetched user info', $user_info );
        }
        else {
            app_false_response( 'Fail to fetch the user data', 501 );
        }
    }

    /**
     * Get the user entry
     */
    public function updateName() : void
    {
        $this->validateJson();

        $req = $this->request;

        if( ! app_properties_found( $req, [
            'name',
        ]) ) 
        {
            app_false_response( 'Required parameters missing', 400 );
        }

        $name = app_clean_input($req->name);

        if( is_numeric($name) || strlen($name) < 2 ) {
            app_false_response('Name seems to be invalid', 200);
        }

        $old_name = $this->user->getInfo('user_fullname', [
            'user_id' => $this->user->getId()
        ]);

        $update = $this->user->update([
            'user_fullname' => $name
        ]);

        if( $update ) {
            app_true_response('Updated successfully', [
                'name' => [
                    'from' => $old_name,
                    'to'   => $name
                ]
            ]);
        }
        else {
            app_false_response('Fail to update name', 503);
        }
    }

    public function listCreate() : void
    {
        $this->validateJson();

        $req = $this->request;

        if( ! app_properties_found( $req, [
            'title',
            'parent',
        ]) ) 
        {
            app_false_response( 'Required parameters missing', 400 );
        }

        $title = app_clean_input($req->title);
        $parent = app_clean_input($req->parent);

        if( is_numeric($title) || strlen($title) < 2 ) {
            app_false_response('List seems to be invalid', 200);
        }

        $list_data = [
            'title' => $title,
            'description' => '',
            'type' => 'list',
            'parent' => $parent,
            'creator' => $this->user->getId(),
            'time' => app_get_datetime(),
        ];

        $list = new Lists();

        if( $list->create($list_data) ) {
            $list_data['id'] = $list->getId();
            app_true_response('List created successfully', [
                'list' => $list_data
            ]);
        }
        else {
            app_false_response('Fail to create list', 503);
        }
    }
 
    public function listUpdate() : void
    {
         $this->validateJson();

        $req = $this->request;

        if( ! app_properties_found( $req, [
            'id',
        ]) ) 
        {
            app_false_response( 'Required parameters missing', 400 );
        }

        $id = app_clean_input($req->id);

        $list = new Lists();
        $list->setId($id);

        if( ! $list->check([
            'list_id' => $id,
            'list_creator' => $this->user->getId(),
            'list_type' => 'list',
        ]) ) {
            app_false_response( 'No list record found', 400 );
        }

        $list_meta = [
            'updated_at' => app_get_datetime(),
        ];

        if( isset($req->title) ) {
            $list_meta['list_title'] = app_clean_input($req->title);
        }
        if( isset($req->description) ) {
            $list_meta['list_description'] = app_clean_input($req->title);
        }
        if( isset($req->parent) ) {
            $list_meta['list_parent'] = app_clean_input($req->parent);
        }

        if( $list->update($list_meta) ) {
            app_true_response('List updated successfully');
        }
        else {
            app_false_response('List up to date', 200);
        }
    }

    public function listDelete() : void
    {
        $this->validateJson();

        $req = $this->request;

        if( ! app_properties_found( $req, [
            'id',
        ]) ) 
        {
            app_false_response( 'Required parameters missing', 400 );
        }

        $id = app_clean_input($req->id);

        $list = new Lists();
        $list->setId($id);

        if( ! $list->check([
            'list_id' => $id,
            'list_creator' => $this->user->getId()
        ]) ) {
            app_false_response( 'No list record found', 404 );
        }

        if( $list->delete() ) {
            app_true_response('List deleted successfully');
        }
        else {
            app_false_response('Fail to delete list', 503);
        }
    }

    public function listDeleteMany() : void
    {
        $this->validateJson();

        $req = $this->request;

        if( ! app_properties_found( $req, [
            'lists',
        ]) ) 
        {
            app_false_response( 'Required parameters missing', 400 );
        }

        $lists = $req->lists;

        $list = new Lists();
        
        $count = 0;
        foreach($lists as $id) {
            $id = app_clean_input($id);

            if( ! $list->check([
                'list_id' => $id,
                'list_creator' => $this->user->getId()
            ]) ) {
                continue;
            }
            
            if( $list->delete($id) ) {
                $count = $count + 1; // count list  delete or not
            }
        }

        if( $count > 0 ) {
            app_true_response("{$count} list  deleted successfully");
        }
        else {
            app_false_response("{$count} list  deleted successfully", 200);
        }
    }

    public function listGet($param) : void
    {
        $id = app_clean_input($param['id']);

        if( 0 === $id ) app_false_response( 'Invalid list', 400 );

        $list = new Lists();
        $list->setId($id);

        // if list  exist by this authenticated user
        if( ! $list->check([
            'list_id' => $id,
            'list_creator' => $this->user->getId()
        ]) ) {
            app_false_response( 'No list record found', 404 );
        }

        // get the list  entry
        $list = $list->get([
            'list_id' => $id,
            'list_creator' => $this->user->getId()
        ]);

        app_true_response('successful', [
            'list' => $list
        ]);
    }

    public function listGetAll() : void
    {
        $list = new Lists();
        $list = $list->select([
            'list_creator' => $this->user->getId(),
            'list_type' => 'list',
        ]);

        app_true_response('successful', [
            'lists' => $list
        ]);
    }

    public function listInviteByEmail() : void
    {
        $this->validateJson();

        $req = $this->request;

        if( ! app_properties_found( $req, [
            'email',
            'id',
        ]) ) 
        {
            app_false_response( 'Required parameters missing', 400 );
        }
        
        $email = app_clean_input($req->email);
        $id = app_clean_input($req->id);

        
        $sender_info = $this->user->getInfo([
            'user_fullname(name)',
            'user_email(email)',
        ]);
        $sender_name = $sender_info['name'];

        if( $email == $sender_info['email'] ) {
            app_false_response('You can\'t invite yourself');
        }

        $list = new Lists();

        // if list  exist by this authenticated user
        if( ! $list->check([
            'list_id' => $id,
            'list_creator' => $this->user->getId()
        ]) ) {
            app_false_response( 'No list record found', 404 );
        }
        
        $list->setId($id);
        
        // validate the email
		if( ! app_validate_email($email) ) {
			// Validate the phone number
			app_false_response( 'Email not valid, please check.', 200 );
        }

        // if the email is an account already, make a login request
        // else make a signup request
        if( $this->user->emailExist($email) ) {
            $request = $this->user->makeRequest('login', $email);
        }
        else {
            $request = $this->user->makeRequest('signup', $email);
        }

		if( !$request['success'] )
		{
			app_false_response('Fail to request to send link');
        }
        
        $link = sprintf(
			'%s/sweet/token/%s', 
			getenv('APP_DOMAIN'),
			$request['code']
		);

		$mail = new Mail();
		$mail->subject = sprintf( 'Hey! you\'re invited on %s', getenv('APP_NAME') );
		$mail->to = $email;
		$mail->body = '';
		$mail->set_default_template_header();
		$mail->append_html( 'Hello,' );
        $mail->append_html( sprintf(
            '<strong>%s</strong> with email (%s) is inviting you to a list  space.',
            ucwords($sender_name),
            $sender_info['email'],
        ) );
        $mail->append_html('Click the link below or paste on your browser to get in.');
		$mail->append_html( $link );
		$mail->append_html( '' );
		$mail->append_html( 'This link will expire in 2 hours and can only be used once.' );
		$mail->append_html( '' );
		$mail->append_html( '' );
		$mail->append_html( 'Cheers,', 'newline' );
		$mail->set_default_template_footer();
		$mail->send();

		app_true_response('An invitation link has been sent to recipent');
    }

    /**
     * Create a new task
     */
    public function taskCreate() : void
    {
         $this->validateJson();

        $req = $this->request;

        if( ! app_properties_found( $req, [
            'title',
            'parent',
        ]) ) 
        {
            app_false_response( 'Required parameters missing', 400 );
        }

        $title = app_clean_input($req->title);
        $parent = app_clean_input($req->parent);

        if( !is_numeric($parent) ) {
            app_false_response('Invalid parent record', 400);
        }

        $list = new Lists();

        // if list  exist by this authenticated user
        if( ! $list->check([
            'list_id' => $parent,
            'list_creator' => $this->user->getId(),
            'list_type' => 'list',
        ]) ) {
            app_false_response( 'No list record found', 404 );
        }

        if( is_numeric($title) || strlen($title) < 2 ) {
            app_false_response('Task seems to be invalid', 200);
        }

        $task_data = [
            'title' => $title,
            'description' => '',
            'parent' => $parent,
            'type' => 'task',
            'creator' => $this->user->getId(),
            'time' => app_get_datetime(),
        ];

        if( $list->create($task_data) ) {
            $task_data['id'] = $list->getId();
            app_true_response('Task created successfully', [
                'task' => $task_data
            ]);
        }
        else {
            app_false_response('Fail to create task', 503);
        }
    }
 
    public function taskUpdate() : void
    {
         $this->validateJson();

        $req = $this->request;

        if( ! app_properties_found( $req, [
            'id',
        ]) ) 
        {
            app_false_response( 'Required parameters missing', 400 );
        }

        $id = app_clean_input($req->id);

        $task = new Lists();
        $task->setId($id);

        if( ! $task->check([
            'list_id' => $id,
            'list_creator' => $this->user->getId(),
            'list_type' => 'task',
        ]) ) {
            app_false_response( 'No task record found', 400 );
        }

        $task_meta = [
            'updated_at' => app_get_datetime(),
        ];

        if( isset($req->title) ) {
            $task_meta['list_title'] = app_clean_input($req->title);
        }
        if( isset($req->description) ) {
            $task_meta['list_description'] = app_clean_input($req->description);
        }
        if( isset($req->status) ) {
         
            if( false === is_int($req->status) || is_bool($req->status) ) {
                app_false_response('Task status is invalid', 200);
            }
            $task_meta['list_status'] = app_clean_input($req->status);
        }
        if( isset($req->parent) ) {
            $task_meta['list_parent'] = app_clean_input($req->parent);

            // if list exist by this authenticated user
            if( ! $list->check([
                'list_id' => $task_meta['list_parent'],
                'list_creator' => $this->user->getId(),
                'list_type' => 'list',
            ]) ) {
                app_false_response( 'No parent record found', 404 );
            }
        }

        if( $task->update($task_meta) ) {
            app_true_response('Task updated successfully');
        }
        else {
            app_false_response('Task up to date', 200);
        }
    }

    public function taskDelete() : void
    {
        $this->validateJson();

        $req = $this->request;

        if( ! app_properties_found( $req, [
            'id',
        ]) ) 
        {
            app_false_response( 'Required parameters missing', 400 );
        }

        $id = app_clean_input($req->id);

        $task = new Lists();
        $task->setId($id);

        if( ! $task->check([
            'list_id' => $id,
            'list_creator' => $this->user->getId(),
            'list_type' => 'task'
        ]) ) {
            app_false_response( 'No task record found', 400 );
        }

        if( $task->delete() ) {
            app_true_response('Task deleted successfully');
        }
        else {
            app_false_response('Fail to delete task', 503);
        }
    }

    public function taskDeleteMany() : void
    {
        $this->validateJson();

        $req = $this->request;

        if( ! app_properties_found( $req, [
            'tasks',
        ]) ) 
        {
            app_false_response( 'Required parameters missing', 400 );
        }

        $tasks = $req->tasks;

        $task = new Lists();
        
        $count = 0;
        foreach($tasks as $id) {
            $id = app_clean_input($id);

            if( ! $task->check([
                'list_id' => $id,
                'list_creator' => $this->user->getId(),
                'list_type' => 'task',
            ]) ) {
                continue;
            }
            
            if( $task->delete($id) ) {
                $count = $count + 1; // count task delete or not
            }
        }

        if( $count > 0 ) {
            app_true_response("{$count} task deleted successfully");
        }
        else {
            app_false_response("{$count} task deleted successfully", 200);
        }
    }

    public function taskGet($param) : void
    {
        $id = app_clean_input($param['id']);

        if( 0 === $id ) app_false_response( 'Invalid task', 400 );

        $task = new Lists();
        $task->setId($id);

        // if task exist by this authenticated user
        if( ! $task->check([
            'list_id' => $id,
            'list_creator' => $this->user->getId(),
            'list_type' => 'task',
        ]) ) {
            app_false_response( 'No task record found', 404 );
        }

        // get the task entry
        $task = $task->get([
            'list_id' => $id,
            'list_creator' => $this->user->getId()
        ]);

        app_true_response('successful', [
            'task' => $task
        ]);
    }

    public function taskGetAll() : void
    {
        $task = new Lists();
        $task = $task->select([
            'list_creator' => $this->user->getId(),
            'list_type' => 'task',
        ]);

        app_true_response('successful', [
            'tasks' => $task
        ]);
    }
}
