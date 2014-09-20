bella.php
========

A lightweight PHP library for quickly building web application.

BellaPHP is a set of useful toolkit that can help you handle almost things, such as: modular, routing, database, curl, file/directory, device detection, and other stuff.

Very easy to install bella.php with composer.phar

      "require": {
          "techpush/bella-php": "dev-master"
      }
  

Website directory structure may look like this (but you can define by any way you want):

      app/
          controllers/
              article.php
          models/
              article.php
          views/
              article.php
      conf/
          development/
          production/
      public/
          css/
          fonts/
          images/
          js/
      storage/
          cache/
          log/
      vendor/
          /techpush/
              bella.php/
                  src/
      
  
  
In the /app/controllers/article.php, you define a controller as below:

      <?php
      
      namespace Bella;
      
      class articleController extends Coordinator {
      	
      	public function parse(){
      		$this->route('/:alias/:id', function($alias, $id){
      			if(!!$id || !!$alias){
      				  $h = $this->loadHandler();
      				  return $h->run($alias, $id);
      			}
      		});
      		return Bella::end();
      	}
      	
      }
      

In the /app/models/article.php, you define the related model as below:

        <?php
        
        namespace Bella;
        
        class articleModel extends Handler {
        	
        	public function run($alias, $id){
        		$conf = Config::get('api');
        		$api = new API($conf->baseURL);
        		
        		$data = $api->get('api/read', ['id'=>$id]);
        		
        		if(!!$data){
        			
        			$view = $this->view;
        			
        			// set layout to render as HTML
        			// it will look at /app/views/layouts/article.html
        			$view->setLayout('article');
        			
        			// register the CSS files
        			$view->registerCSS(['base.css', 'default.css', 'article.css', 'fontello.css']);
        			
        			// register the javascript libraries
        			$view->registerLibs(['highlight.pack', 'socialite.min']);
        			
        			// set metadata to the page
        			$view->setHeader([
        				'title' => $data->title,
        				'url' => $data->link,
        				'canonical' => $data->canonical,
        				'creator' => $data->author,
        				'description' => $data->description,
        				'image' => $data->image
        			]);
        			return $view->render(['article' => $data]);
        		}
        		
        		return Bella::end();
        	}
        	
        }


BellaPHP uses Handlebars as template engine, /app/views/layouts/article.html looks like this:

      <div class="article-title" itemprop="name">{{{article.title}}}</div>
      <div class="article-summary">
          <span class="article-pubTime" value="{{datetime}}" itemprop="datePublished" content="{{datetime}}">
              {{article.datetime}}
          </span>,
          <span itemprop="author" itemscope itemtype="http://schema.org/Person">
              <span itemprop="name">{{{article.author}}}</span>
          </span>
      <div class="article-content" itemprop="articleBody">{{{article.content}}}</div>


To see it in real world, please visit my website: http://techpush.net/
