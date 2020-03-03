# Table of Contents

1. [intro](#intro) about this
2. [start](#start) an task
3. [test](#test) your script
4. [default value](#default-value) for you argument
5. [multiple values](#multiple-values) for you argument
6. [macros](#macros) for reusable code


##### Intro

This tutorial we will demonstrate some key aspect for defining tasks. To read more about the options you can use the debug command:

```
a debug:config:dump-reference tasks
```

or read an dump [here](tasks_reference.md)

##### Start

To create an simple greet console command we need create an config file `a.yaml` and add the following:

```
tasks:
  example.greet:
    description: An greet example command
    args:
      name: ~
    exec: echo "hello {{ arg('name') }}"
```

So with a couple of lines we have created and simple command that takes an argument and does an echo with the given argument.

To run this command you can run the the following:

```
a example:greet philip
```

And should see something like:

```
~$ a example:greet philip
hello philip
```

##### Test

Because by default tasks will be converted to an shell script and all task will have an `--dump` option to print the script to stdout instead of being executed. 

So by using the dump and pipe the output to an application like `bash -n` or [shellcheck](https://www.shellcheck.net/) we create an dry-run and do an check for syntax errors.

To demonstrate this we are going to break our last command: 

```
tasks:
  example.greet:
    description: An greet example command
    args:
      name: ~
    # removed the last qoute
    exec: echo "hello {{ arg('name') }}

```

So if run now like:

```
example:greet foo --dump | bash -nv
```

We should get something like:

```e
#!/bin/bash
set -e
echo "hello foo
bash: line 3: unexpected EOF while looking for matching `"'
bash: line 4: syntax error: unexpected end of file
```

Or with shellcheck:

```
example:greet foo --dump | bash -nv
```

which will give us something like:

```

In - line 3:
echo "hello foo
^-- SC1009: The mentioned syntax error was in this simple command.
     ^-- SC1073: Couldn't parse this double quoted string. Fix to allow more checks.


In - line 4:

^-- SC1072: Expected end of double quoted string. Fix any mentioned problems and try again.



```

##### Default Value

In the last example we have set an null value to the name argument and if just want to have an default value assigned to this we only have to change the value to something we want like:

```
tasks:
  example.greet:
    description: An greet example command
    args:
      name: $USER
    exec: echo "hello {{ arg('name') }}"
```

This will now dump this when executed with oud any arguments:

```
$ bin/a example:greet  --dump 
#!/bin/bash
set -e
echo "hello $USER"

```

Or with an argument:

```
bin/a example:greet foo --dump 
#!/bin/bash
set -e
echo "hello foo"
```

to inspect out command we can also dump the processed config which is used by the application with the `a debug:config:dump` command.

##### Multiple Values

If our argument needs to support multiple `name` values we have to describe our argument more verbose and change the mode to `is_array`, make the default an array and handle/format argument because this will be an array. To read more about this jou can see the `debug:config:dump-reference tasks.name.args` command which will give you an comprehensive description about the argument options.

So wo are going to change the tasks to something like this:

```
tasks:
  example.greet:
    description: An greet example command
    args:
      name: 
        mode: is_array          
        default: [$USER]
    exec: echo "hello {{ arg('name')|join(', ', ' and ')  }}"
```

Or an other example where we create an little loop to print every name on an new line and make the argument required:

```
tasks:
  example.greet:
    description: An greet example command
    args:
      name: 
        mode: is_array|required
    exec: |
      {% for name in  arg('name') %}
          echo "hello {{ name }}"
      {% endfor %}
```

##### Macros

If we want te reuse a peace of code we can create an [macro](https://twig.symfony.com/doc/2.x/tags/macro.html). There are 2 ways of defining macros where the first one is by just creating an macros like you would normally do:

```
tasks:
  example.greet:
    description: An greet example command
    macros:
      print: | 
        {% macro print(input, what)  %}
            {% for name in args('name') %} 
                echo "{{ what }} {{ name }}"
            {% endfor %}
        {% endmacro %}
    ...
```

And there is an shorthand version that also bit more readable:

```
tasks:
  example.greet:
    description: An greet example command
    macros:
      print:
        args: [input, what]
        code |
          {% for name in args('name') %} 
            echo "{{ what }} {{ name }}"
          {% endfor %}
    ...
```

These could also be defined global to make the accessible to all task but for this example we will scope them to the task. 

To use this macro we just have to call the macro as done in the following example:

```
tasks:
  example.greet:
    description: An greet example command
    macros:
      print:
        args: [input, what]
        code: |
          {%- for user in arg('name') -%}
            echo "{{ what }} {{ user }}"
          {% endfor -%}
    args:
      name:
        mode: is_array
        default: [$USER]
    exec:
      - "{{ _self.print(input, 'hello')}}"
      - "{{ _self.print(input, 'good bye')}}"
```
