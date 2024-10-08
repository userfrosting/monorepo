"use strict";Object.defineProperty(exports,Symbol.toStringTag,{value:"Module"});const e=require("vue"),a=require("./moment-Bp7fbP4q.cjs"),u={class:"uk-text-meta"},c={__name:"UsersView",setup(m){return(p,t)=>{const l=e.resolveComponent("UFHeaderPage"),n=e.resolveComponent("UFSprunjeHeader"),s=e.resolveComponent("RouterLink"),r=e.resolveComponent("UFSprunjeColumn"),d=e.resolveComponent("UFSprunjeTable"),i=e.resolveComponent("UFCardBox");return e.openBlock(),e.createElementBlock(e.Fragment,null,[e.createVNode(l,{title:"Users",caption:`A listing of the users for your site. Provides management tools including the ability to
        edit user details, manually activate users, enable/disable users, and more.`}),e.createVNode(i,null,{default:e.withCtx(()=>[e.createVNode(d,{dataUrl:"/api/users"},{header:e.withCtx(()=>[e.createVNode(n,{sort:"name"},{default:e.withCtx(()=>t[0]||(t[0]=[e.createTextVNode("User")])),_:1}),e.createVNode(n,{sort:"last_activity"},{default:e.withCtx(()=>t[1]||(t[1]=[e.createTextVNode("Last Activity")])),_:1}),e.createVNode(n,{sort:"status"},{default:e.withCtx(()=>t[2]||(t[2]=[e.createTextVNode("Status")])),_:1}),e.createVNode(n,null,{default:e.withCtx(()=>t[3]||(t[3]=[e.createTextVNode("Actions")])),_:1})]),body:e.withCtx(({item:o})=>[e.createVNode(r,null,{default:e.withCtx(()=>[e.createElementVNode("strong",null,[e.createVNode(s,{to:{name:"admin.user",params:{user_name:o.user_name}}},{default:e.withCtx(()=>[e.createTextVNode(e.toDisplayString(o.full_name)+" ("+e.toDisplayString(o.user_name)+") ",1)]),_:2},1032,["to"])]),e.createElementVNode("div",u,e.toDisplayString(o.email),1)]),_:2},1024),e.createVNode(r,null,{default:e.withCtx(()=>[e.createElementVNode("div",null,e.toDisplayString(e.unref(a.hooks)(o.last_activity.occurred_at).format("dddd")),1),e.createElementVNode("div",null,e.toDisplayString(e.unref(a.hooks)(o.last_activity.occurred_at).format("MMM Do, YYYY h:mm a")),1),e.createElementVNode("i",null,e.toDisplayString(o.last_activity.description),1)]),_:2},1024),e.createVNode(r),e.createVNode(r)]),_:1})]),_:1})],64)}}};exports.default=c;
