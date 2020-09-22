```
tasks:

    # Prototype
    name:

        # This task will be used as template to be merged with task that extend this when set to true.
        abstract:             false

        # Abstract templates to merge config with.
        extends:              []
        help:                 null
        description:          null
        hidden:               false

        # Environment variables that will be used run this application.
        # 
        # When not provided it will use the environment of parent process.
        envs:

            # Prototype
            name:                 ~

        # Macros that can be used reusable logic the templates, see:
        # 
        #     https://twig.symfony.com/doc/2.x/tags/macro.html
        # 
        # All macros are autoloaded and should be called with the 
        # `_self.` prefix. 
        # 
        # To scope macros to an task you should define them under
        # an task and when set to the root all plugins have access
        # to that macro.  
        macros:

            # Prototype
            name:
                code:                 ~
                args:                 []

        # An argument can be as simple as key with null value which will normalized to an 
        # argument that requires an value:
        # 
        # tasks:
        #     example:
        #         args:
        #             foo: ~
        #             
        # will be normalized to:
        # 
        # tasks:
        #     example:
        #         opts:
        #             foo: 
        #                 mode: 1 # InputArgument::REQUIRED
        #                 
        #           
        # An other short hand is just to provide an key value pair which will be normalized to 
        # an argument where the value is required and default id the value:
        # 
        # tasks:
        #     example:
        #         artg:
        #             foo: bar
        #             
        # will be normalized to:
        # 
        # tasks:
        #     example:
        #         artg:
        #             foo: 
        #                 mode: 1 # InputArgument::VALUE_REQUIRED
        #                 default: bar
        args:

            # Prototype
            name:
                name:                 ~

                # similar to opts mode (see: tasks.name.opts.name.mode) except it will use the InputArgument::* constants
                mode:                 null
                description:          ''
                default:              null

        # An option can be as simple as key with null value which will normalized to an 
        # option that not accepts an value (bool option):
        # 
        # tasks:
        #     example:
        #         opts:
        #             foo: ~
        #             
        # will be normalized to:
        # 
        # tasks:
        #     example:
        #         opts:
        #             foo: 
        #                 mode: 1 # InputOption::VALUE_NONE
        #                 
        #           
        # An other short hand is just to provide an key value pair which will be normalized to 
        # an option with and value is required and the value is the default:
        # 
        # tasks:
        #     example:
        #         opts:
        #             foo: bar
        #             
        # will be normalized to:
        # 
        # tasks:
        #     example:
        #         opts:
        #             foo: 
        #                 mode: 2 # InputOption::VALUE_REQUIRED
        #                 default: bar
        # 
        opts:

            # Prototype
            name:
                name:                 ~
                shortcut:             null

                # The mode node supports multiple formats that will be normalized to an acceptable $mode argument value for the 
                # InputOption (one of InputOption::VALUE_*). 
                # 
                # When string is provided it will try te resolve that to one of the InputOption::VALUE_* constants by converting 
                # the string to uppercase, prefixing with VALUE_ when it not starts with that and splitting the string on |. 
                # 
                # 
                #     tasks:
                #         example:
                #             opts:
                #                 foo: 
                #                     # as string
                #                     mode: is_array|required
                #                     
                #                     # as array
                #                     # mode: 
                #                     #    - is_array
                #                     #    - required
                #                     
                #                     # as int 
                #                     # mode: 10                    
                #                     
                #     will be normalized to                 
                #     
                #     tasks:
                #         example:
                #             opts:
                #                 foo: 
                #                     mode: 10 # InputOption::VALUE_REQUIRED|InputOption::VALUE_IS_ARRAY
                mode:                 null
                description:          ''
                default:              null
        pre:

            # Prototype: pre hook which accepts an string, array of strings or more verbose with array 
            # where every entry has an exec and weight so you can control merge position. 
            -
                exec:                 ~
                weight:               0
        post:

            # Prototype: post hook which accepts an string, array of strings or more verbose with array 
            # where every entry has an exec and weight so you can control merge position. 
            -
                exec:                 ~
                weight:               0

        # Similar to pre and post with the exception this won`t be merged
        # and only accepts a strings or array of strings as value.
        exec:                 []

```