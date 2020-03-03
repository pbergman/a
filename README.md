# A

This is an build automation tool for creating and executing shell scripts and is based on principles of [z](https://github.com/zicht/z). 

A is build around the principle that all task become (twig) templates which can be included (als see `debug:print-templates` command). 

Tasks will be split in different set of templates (pre, exec, post and grouped) which makes it possible to only include an part of an task or all. To give an better example we can create an tasks like:

```
tasks:
  example.template:
    pre:  echo "post"
    exec: echo "exec"
    post: echo "exec"
```  

and run `bin/a debug:print-templates` which will give us:

```
  template:   example:template
  reference:  
  value:      {% include 'example:template::pre[0]' %}
              {% include 'example:template::exec[0]' %}
              {% include 'example:template::post[0]' %}
                             
  
  template:   example:template::pre
  reference:  
  value:      {% include 'example:template::pre[0]' %}
                             
  
  template:   example:template::pre[0]
  reference:  0::example.template::pre[0]
  value:      echo "post"
                             
  
  template:   example:template::exec
  reference:  
  value:      {% include 'example:template::exec[0]' %}
                             
  
  template:   example:template::exec[0]
  reference:  0::example.template::exec[0]
  value:      echo "exec"
                             
  
  template:   example:template::post
  reference:  
  value:      {% include 'example:template::post[0]' %}
                             
  
  template:   example:template::post[0]
  reference:  0::example.template::post[0]
  value:      echo "exec"
                          
```

### Setup

For locating plugins the application will use the `A_PLUGIN_PATH` environment variable to search and include plugins. 

This can be an glob pattern and joined by an `:` similar the `PATH` variable.

To set this permanently you can add this to your `~/.profile` oer `~/.bashrc`

```
export A_PLUGIN_PATH="/home/user/workspace/php/lib/a-plugins/*:."
```

This application will check by default for an `a.yaml` in the current working dir but this can be changed with the `--config` option. This config file should hold all tasks and config for running the application for an given project.

### Tasks

This application is build around the principle of creating quick and easily tasks in the yaml file which will be converted to [symfony console commands](https://symfony.com/doc/current/console.html). 

To read more about the options use the debug command:

```
a debug:config:dump-reference tasks
```

or read the docs [here](docs/tasks_example.md)
-->
