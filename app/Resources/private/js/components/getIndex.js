/* ===========================================================================
   Get index
   =========================================================================== */

export default (node) => {
  const childs = node.parentNode.children;
  let i = 0;

  for (i = 0; i < childs.length; i++) {
    if (node == childs[i]) break;
  }

  return i+1;
};
