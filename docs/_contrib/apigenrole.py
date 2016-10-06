from docutils import nodes, utils
from docutils.parsers.rst.roles import set_classes

# I cant figure out how the hell to import this so I'm just gonna forget it for now

def apigen_role(name, rawtext, text, lineno, inliner, options={}, content=[]):
    """Link to API Docs page.

    Returns 2 part tuple containing list of nodes to insert into the
    document and a list of system messages.  Both are allowed to be
    empty.

    :param name: The role name used in the document.
    :param rawtext: The entire markup snippet, with role.
    :param text: The text marked with the role.
    :param lineno: The line number where rawtext appears in the input.
    :param inliner: The inliner instance that called us.
    :param options: Directive options for customization.
    :param content: The directive content for customization.
    """
    try:
        class_name = text.replace('\\', '.')
        if text[0:1] == '.':
            class_name = class_name[1:]
        if class_name == "":
            raise ValueError
    except ValueError:
        msg = inliner.reporter.error(
            'Class name must be a valid fully qualified class name; '
            '"%s" is invalid.' % text, line=lineno)
        prb = inliner.problematic(rawtext, rawtext, msg)
        return [prb], [msg]
    app = inliner.document.settings.env.app
    node = make_link_node(rawtext, app, 'class', class_name, options)
    return [node], []

def make_link_node(rawtext, app, type, slug, options):
    """Create a link to an ApiGen API docs page.

    :param rawtext: Text being replaced with link node.
    :param app: Sphinx application context
    :param type: Item type (class, namespace, etc.)
    :param slug: ID of the thing to link to
    :param options: Options dictionary passed to role func.
    """
    #
    try:
        base = app.config.apigen_docs_uri
        if not base:
            raise AttributeError
    except AttributeError, err:
        raise ValueError('apigen_docs_uri configuration value is not set (%s)' % str(err))
    # Build API docs link
    slash = '/' if base[-1] != '/' else ''
    ref = base + slash + type + '-' + slug + '.html'
    set_classes(options)
    node = nodes.reference(rawtext, type + ' ' + utils.unescape(slug), refuri=ref,
                           **options)
    return node

def setup(app):
    """Install the plugin.

    :param app: Sphinx application context.
    """
    app.info('Initializing Api Class plugin')
    app.add_role('apiclass', apigen_role)
#    app.add_role('apins', apigen_namespace_role)
    app.add_config_value('apigen_docs_uri', None, 'env')
    return
