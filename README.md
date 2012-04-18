# [Lithium PHP](http://lithify.me) Plugin to allow for savvy template inheritance
***
> Don't get too excited, this project has only just started but here's what I plan to accomplish, and why.
***

## The Why
I recently created a [Li3 Smarty](https://github.com/joseym/li3_smarty) plugin for the organization I am employed with.

I'm fairly vocal about my distaste for PHP Templating languages, especially Smarty, however I can agree with one thing

> __Smarty Docs__: [Template Inheritance] keeps template management minimal and efficient, since each template only contains the differences from the template it extends. 

I agree! Sadly Lithium doesn't handle templates and views in this manner.

Before the Smarty project I had made an attempt to add another layout to the rendering order with [Li3 Partials](https://github.com/joseym/li3_partials) which allows you to designate sections in your layout and pass code to those sections from your view.

It was a step in the right direction but still seems a bit lacking.

## What I Plan to Accomplish
My goal is to modify the way lithium renders views. In your view you will be able to tell the view what other view it is extending from. The parent view will have sections blocked off that will receive code from it's child.

The parent view could just as easily extend another view, and so on, so forth.

The very tipity-top view would extend a layout, which is essentially another view. That layout could extend other layouts. All with block designations that would allow you to pass default content or extend from a child.

> Useful if you need to have slight template layout modifications from different pages but want to keep the same basics from a layout or two.

I also plan to store the content that was set as default so that if you override a block from a child you can pass in what was initially passed in the parent.

###Enough Rambling
I'm going to start building it now.