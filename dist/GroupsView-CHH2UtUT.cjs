"use strict";Object.defineProperty(exports,Symbol.toStringTag,{value:"Module"});const e=require("vue"),s=require("./_plugin-vue_export-helper-BHFhmbuH.cjs"),i={};function p(_,t){const l=e.resolveComponent("UFHeaderPage"),o=e.resolveComponent("UFSprunjeHeader"),a=e.resolveComponent("RouterLink"),n=e.resolveComponent("UFSprunjeColumn"),u=e.resolveComponent("UFSprunjeTable"),d=e.resolveComponent("UFCardBox");return e.openBlock(),e.createElementBlock(e.Fragment,null,[e.createVNode(l,{title:"Groups",caption:"A listing of the groups for your site.  Provides management tools for editing and deleting groups."}),e.createVNode(d,null,{default:e.withCtx(()=>[e.createVNode(u,{dataUrl:"/api/groups"},{header:e.withCtx(()=>[e.createVNode(o,null,{default:e.withCtx(()=>t[0]||(t[0]=[e.createTextVNode("Groups")])),_:1}),e.createVNode(o,null,{default:e.withCtx(()=>t[1]||(t[1]=[e.createTextVNode("Description")])),_:1}),e.createVNode(o,null,{default:e.withCtx(()=>t[2]||(t[2]=[e.createTextVNode("Actions")])),_:1})]),body:e.withCtx(({item:r})=>[e.createVNode(n,null,{default:e.withCtx(()=>[e.createElementVNode("strong",null,[e.createVNode(a,{to:{name:"admin.group",params:{slug:r.slug}}},{default:e.withCtx(()=>[e.createTextVNode(e.toDisplayString(r.name),1)]),_:2},1032,["to"])])]),_:2},1024),e.createVNode(n,null,{default:e.withCtx(()=>[e.createTextVNode(e.toDisplayString(r.description),1)]),_:2},1024),e.createVNode(n)]),_:1})]),_:1})],64)}const c=s._export_sfc(i,[["render",p]]);exports.default=c;
