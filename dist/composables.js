import { a as le, b as k } from "./types-tJXMLagF.js";
import { defineStore as nt } from "pinia";
function _e(e, t) {
  return function() {
    return e.apply(t, arguments);
  };
}
const { toString: ot } = Object.prototype, { getPrototypeOf: fe } = Object, V = /* @__PURE__ */ ((e) => (t) => {
  const r = ot.call(t);
  return e[r] || (e[r] = r.slice(8, -1).toLowerCase());
})(/* @__PURE__ */ Object.create(null)), x = (e) => (e = e.toLowerCase(), (t) => V(t) === e), K = (e) => (t) => typeof t === e, { isArray: B } = Array, D = K("undefined");
function st(e) {
  return e !== null && !D(e) && e.constructor !== null && !D(e.constructor) && O(e.constructor.isBuffer) && e.constructor.isBuffer(e);
}
const Pe = x("ArrayBuffer");
function it(e) {
  let t;
  return typeof ArrayBuffer < "u" && ArrayBuffer.isView ? t = ArrayBuffer.isView(e) : t = e && e.buffer && Pe(e.buffer), t;
}
const at = K("string"), O = K("function"), Le = K("number"), $ = (e) => e !== null && typeof e == "object", ct = (e) => e === !0 || e === !1, z = (e) => {
  if (V(e) !== "object")
    return !1;
  const t = fe(e);
  return (t === null || t === Object.prototype || Object.getPrototypeOf(t) === null) && !(Symbol.toStringTag in e) && !(Symbol.iterator in e);
}, ut = x("Date"), lt = x("File"), ft = x("Blob"), dt = x("FileList"), pt = (e) => $(e) && O(e.pipe), ht = (e) => {
  let t;
  return e && (typeof FormData == "function" && e instanceof FormData || O(e.append) && ((t = V(e)) === "formdata" || // detect form-data instance
  t === "object" && O(e.toString) && e.toString() === "[object FormData]"));
}, mt = x("URLSearchParams"), [gt, bt, yt, wt] = ["ReadableStream", "Request", "Response", "Headers"].map(x), Et = (e) => e.trim ? e.trim() : e.replace(/^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g, "");
function q(e, t, { allOwnKeys: r = !1 } = {}) {
  if (e === null || typeof e > "u")
    return;
  let n, o;
  if (typeof e != "object" && (e = [e]), B(e))
    for (n = 0, o = e.length; n < o; n++)
      t.call(null, e[n], n, e);
  else {
    const s = r ? Object.getOwnPropertyNames(e) : Object.keys(e), i = s.length;
    let c;
    for (n = 0; n < i; n++)
      c = s[n], t.call(null, e[c], c, e);
  }
}
function Be(e, t) {
  t = t.toLowerCase();
  const r = Object.keys(e);
  let n = r.length, o;
  for (; n-- > 0; )
    if (o = r[n], t === o.toLowerCase())
      return o;
  return null;
}
const N = typeof globalThis < "u" ? globalThis : typeof self < "u" ? self : typeof window < "u" ? window : global, Fe = (e) => !D(e) && e !== N;
function ne() {
  const { caseless: e } = Fe(this) && this || {}, t = {}, r = (n, o) => {
    const s = e && Be(t, o) || o;
    z(t[s]) && z(n) ? t[s] = ne(t[s], n) : z(n) ? t[s] = ne({}, n) : B(n) ? t[s] = n.slice() : t[s] = n;
  };
  for (let n = 0, o = arguments.length; n < o; n++)
    arguments[n] && q(arguments[n], r);
  return t;
}
const Rt = (e, t, r, { allOwnKeys: n } = {}) => (q(t, (o, s) => {
  r && O(o) ? e[s] = _e(o, r) : e[s] = o;
}, { allOwnKeys: n }), e), St = (e) => (e.charCodeAt(0) === 65279 && (e = e.slice(1)), e), Ot = (e, t, r, n) => {
  e.prototype = Object.create(t.prototype, n), e.prototype.constructor = e, Object.defineProperty(e, "super", {
    value: t.prototype
  }), r && Object.assign(e.prototype, r);
}, Tt = (e, t, r, n) => {
  let o, s, i;
  const c = {};
  if (t = t || {}, e == null) return t;
  do {
    for (o = Object.getOwnPropertyNames(e), s = o.length; s-- > 0; )
      i = o[s], (!n || n(i, e, t)) && !c[i] && (t[i] = e[i], c[i] = !0);
    e = r !== !1 && fe(e);
  } while (e && (!r || r(e, t)) && e !== Object.prototype);
  return t;
}, At = (e, t, r) => {
  e = String(e), (r === void 0 || r > e.length) && (r = e.length), r -= t.length;
  const n = e.indexOf(t, r);
  return n !== -1 && n === r;
}, xt = (e) => {
  if (!e) return null;
  if (B(e)) return e;
  let t = e.length;
  if (!Le(t)) return null;
  const r = new Array(t);
  for (; t-- > 0; )
    r[t] = e[t];
  return r;
}, vt = /* @__PURE__ */ ((e) => (t) => e && t instanceof e)(typeof Uint8Array < "u" && fe(Uint8Array)), Ct = (e, t) => {
  const r = (e && e[Symbol.iterator]).call(e);
  let n;
  for (; (n = r.next()) && !n.done; ) {
    const o = n.value;
    t.call(e, o[0], o[1]);
  }
}, jt = (e, t) => {
  let r;
  const n = [];
  for (; (r = e.exec(t)) !== null; )
    n.push(r);
  return n;
}, Nt = x("HTMLFormElement"), _t = (e) => e.toLowerCase().replace(
  /[-_\s]([a-z\d])(\w*)/g,
  function(t, r, n) {
    return r.toUpperCase() + n;
  }
), be = (({ hasOwnProperty: e }) => (t, r) => e.call(t, r))(Object.prototype), Pt = x("RegExp"), Ue = (e, t) => {
  const r = Object.getOwnPropertyDescriptors(e), n = {};
  q(r, (o, s) => {
    let i;
    (i = t(o, s, e)) !== !1 && (n[s] = i || o);
  }), Object.defineProperties(e, n);
}, Lt = (e) => {
  Ue(e, (t, r) => {
    if (O(e) && ["arguments", "caller", "callee"].indexOf(r) !== -1)
      return !1;
    const n = e[r];
    if (O(n)) {
      if (t.enumerable = !1, "writable" in t) {
        t.writable = !1;
        return;
      }
      t.set || (t.set = () => {
        throw Error("Can not rewrite read-only method '" + r + "'");
      });
    }
  });
}, Bt = (e, t) => {
  const r = {}, n = (o) => {
    o.forEach((s) => {
      r[s] = !0;
    });
  };
  return B(e) ? n(e) : n(String(e).split(t)), r;
}, Ft = () => {
}, Ut = (e, t) => e != null && Number.isFinite(e = +e) ? e : t, Z = "abcdefghijklmnopqrstuvwxyz", ye = "0123456789", ke = {
  DIGIT: ye,
  ALPHA: Z,
  ALPHA_DIGIT: Z + Z.toUpperCase() + ye
}, kt = (e = 16, t = ke.ALPHA_DIGIT) => {
  let r = "";
  const { length: n } = t;
  for (; e--; )
    r += t[Math.random() * n | 0];
  return r;
};
function Dt(e) {
  return !!(e && O(e.append) && e[Symbol.toStringTag] === "FormData" && e[Symbol.iterator]);
}
const qt = (e) => {
  const t = new Array(10), r = (n, o) => {
    if ($(n)) {
      if (t.indexOf(n) >= 0)
        return;
      if (!("toJSON" in n)) {
        t[o] = n;
        const s = B(n) ? [] : {};
        return q(n, (i, c) => {
          const l = r(i, o + 1);
          !D(l) && (s[c] = l);
        }), t[o] = void 0, s;
      }
    }
    return n;
  };
  return r(e, 0);
}, It = x("AsyncFunction"), Mt = (e) => e && ($(e) || O(e)) && O(e.then) && O(e.catch), De = ((e, t) => e ? setImmediate : t ? ((r, n) => (N.addEventListener("message", ({ source: o, data: s }) => {
  o === N && s === r && n.length && n.shift()();
}, !1), (o) => {
  n.push(o), N.postMessage(r, "*");
}))(`axios@${Math.random()}`, []) : (r) => setTimeout(r))(
  typeof setImmediate == "function",
  O(N.postMessage)
), zt = typeof queueMicrotask < "u" ? queueMicrotask.bind(N) : typeof process < "u" && process.nextTick || De, a = {
  isArray: B,
  isArrayBuffer: Pe,
  isBuffer: st,
  isFormData: ht,
  isArrayBufferView: it,
  isString: at,
  isNumber: Le,
  isBoolean: ct,
  isObject: $,
  isPlainObject: z,
  isReadableStream: gt,
  isRequest: bt,
  isResponse: yt,
  isHeaders: wt,
  isUndefined: D,
  isDate: ut,
  isFile: lt,
  isBlob: ft,
  isRegExp: Pt,
  isFunction: O,
  isStream: pt,
  isURLSearchParams: mt,
  isTypedArray: vt,
  isFileList: dt,
  forEach: q,
  merge: ne,
  extend: Rt,
  trim: Et,
  stripBOM: St,
  inherits: Ot,
  toFlatObject: Tt,
  kindOf: V,
  kindOfTest: x,
  endsWith: At,
  toArray: xt,
  forEachEntry: Ct,
  matchAll: jt,
  isHTMLForm: Nt,
  hasOwnProperty: be,
  hasOwnProp: be,
  // an alias to avoid ESLint no-prototype-builtins detection
  reduceDescriptors: Ue,
  freezeMethods: Lt,
  toObjectSet: Bt,
  toCamelCase: _t,
  noop: Ft,
  toFiniteNumber: Ut,
  findKey: Be,
  global: N,
  isContextDefined: Fe,
  ALPHABET: ke,
  generateString: kt,
  isSpecCompliantForm: Dt,
  toJSONObject: qt,
  isAsyncFn: It,
  isThenable: Mt,
  setImmediate: De,
  asap: zt
};
function g(e, t, r, n, o) {
  Error.call(this), Error.captureStackTrace ? Error.captureStackTrace(this, this.constructor) : this.stack = new Error().stack, this.message = e, this.name = "AxiosError", t && (this.code = t), r && (this.config = r), n && (this.request = n), o && (this.response = o);
}
a.inherits(g, Error, {
  toJSON: function() {
    return {
      // Standard
      message: this.message,
      name: this.name,
      // Microsoft
      description: this.description,
      number: this.number,
      // Mozilla
      fileName: this.fileName,
      lineNumber: this.lineNumber,
      columnNumber: this.columnNumber,
      stack: this.stack,
      // Axios
      config: a.toJSONObject(this.config),
      code: this.code,
      status: this.response && this.response.status ? this.response.status : null
    };
  }
});
const qe = g.prototype, Ie = {};
[
  "ERR_BAD_OPTION_VALUE",
  "ERR_BAD_OPTION",
  "ECONNABORTED",
  "ETIMEDOUT",
  "ERR_NETWORK",
  "ERR_FR_TOO_MANY_REDIRECTS",
  "ERR_DEPRECATED",
  "ERR_BAD_RESPONSE",
  "ERR_BAD_REQUEST",
  "ERR_CANCELED",
  "ERR_NOT_SUPPORT",
  "ERR_INVALID_URL"
  // eslint-disable-next-line func-names
].forEach((e) => {
  Ie[e] = { value: e };
});
Object.defineProperties(g, Ie);
Object.defineProperty(qe, "isAxiosError", { value: !0 });
g.from = (e, t, r, n, o, s) => {
  const i = Object.create(qe);
  return a.toFlatObject(e, i, function(c) {
    return c !== Error.prototype;
  }, (c) => c !== "isAxiosError"), g.call(i, e.message, t, r, n, o), i.cause = e, i.name = e.name, s && Object.assign(i, s), i;
};
const Ht = null;
function oe(e) {
  return a.isPlainObject(e) || a.isArray(e);
}
function Me(e) {
  return a.endsWith(e, "[]") ? e.slice(0, -2) : e;
}
function we(e, t, r) {
  return e ? e.concat(t).map(function(n, o) {
    return n = Me(n), !r && o ? "[" + n + "]" : n;
  }).join(r ? "." : "") : t;
}
function Jt(e) {
  return a.isArray(e) && !e.some(oe);
}
const Wt = a.toFlatObject(a, {}, null, function(e) {
  return /^is[A-Z]/.test(e);
});
function G(e, t, r) {
  if (!a.isObject(e))
    throw new TypeError("target must be an object");
  t = t || new FormData(), r = a.toFlatObject(r, {
    metaTokens: !0,
    dots: !1,
    indexes: !1
  }, !1, function(m, h) {
    return !a.isUndefined(h[m]);
  });
  const n = r.metaTokens, o = r.visitor || u, s = r.dots, i = r.indexes, c = (r.Blob || typeof Blob < "u" && Blob) && a.isSpecCompliantForm(t);
  if (!a.isFunction(o))
    throw new TypeError("visitor must be a function");
  function l(m) {
    if (m === null) return "";
    if (a.isDate(m))
      return m.toISOString();
    if (!c && a.isBlob(m))
      throw new g("Blob is not supported. Use a Buffer instead.");
    return a.isArrayBuffer(m) || a.isTypedArray(m) ? c && typeof Blob == "function" ? new Blob([m]) : Buffer.from(m) : m;
  }
  function u(m, h, d) {
    let E = m;
    if (m && !d && typeof m == "object") {
      if (a.endsWith(h, "{}"))
        h = n ? h : h.slice(0, -2), m = JSON.stringify(m);
      else if (a.isArray(m) && Jt(m) || (a.isFileList(m) || a.endsWith(h, "[]")) && (E = a.toArray(m)))
        return h = Me(h), E.forEach(function(T, R) {
          !(a.isUndefined(T) || T === null) && t.append(
            // eslint-disable-next-line no-nested-ternary
            i === !0 ? we([h], R, s) : i === null ? h : h + "[]",
            l(T)
          );
        }), !1;
    }
    return oe(m) ? !0 : (t.append(we(d, h, s), l(m)), !1);
  }
  const f = [], p = Object.assign(Wt, {
    defaultVisitor: u,
    convertValue: l,
    isVisitable: oe
  });
  function y(m, h) {
    if (!a.isUndefined(m)) {
      if (f.indexOf(m) !== -1)
        throw Error("Circular reference detected in " + h.join("."));
      f.push(m), a.forEach(m, function(d, E) {
        (!(a.isUndefined(d) || d === null) && o.call(
          t,
          d,
          a.isString(E) ? E.trim() : E,
          h,
          p
        )) === !0 && y(d, h ? h.concat(E) : [E]);
      }), f.pop();
    }
  }
  if (!a.isObject(e))
    throw new TypeError("data must be an object");
  return y(e), t;
}
function Ee(e) {
  const t = {
    "!": "%21",
    "'": "%27",
    "(": "%28",
    ")": "%29",
    "~": "%7E",
    "%20": "+",
    "%00": "\0"
  };
  return encodeURIComponent(e).replace(/[!'()~]|%20|%00/g, function(r) {
    return t[r];
  });
}
function de(e, t) {
  this._pairs = [], e && G(e, this, t);
}
const ze = de.prototype;
ze.append = function(e, t) {
  this._pairs.push([e, t]);
};
ze.toString = function(e) {
  const t = e ? function(r) {
    return e.call(this, r, Ee);
  } : Ee;
  return this._pairs.map(function(r) {
    return t(r[0]) + "=" + t(r[1]);
  }, "").join("&");
};
function Vt(e) {
  return encodeURIComponent(e).replace(/%3A/gi, ":").replace(/%24/g, "$").replace(/%2C/gi, ",").replace(/%20/g, "+").replace(/%5B/gi, "[").replace(/%5D/gi, "]");
}
function He(e, t, r) {
  if (!t)
    return e;
  const n = r && r.encode || Vt, o = r && r.serialize;
  let s;
  if (o ? s = o(t, r) : s = a.isURLSearchParams(t) ? t.toString() : new de(t, r).toString(n), s) {
    const i = e.indexOf("#");
    i !== -1 && (e = e.slice(0, i)), e += (e.indexOf("?") === -1 ? "?" : "&") + s;
  }
  return e;
}
class Re {
  constructor() {
    this.handlers = [];
  }
  /**
   * Add a new interceptor to the stack
   *
   * @param {Function} fulfilled The function to handle `then` for a `Promise`
   * @param {Function} rejected The function to handle `reject` for a `Promise`
   *
   * @return {Number} An ID used to remove interceptor later
   */
  use(t, r, n) {
    return this.handlers.push({
      fulfilled: t,
      rejected: r,
      synchronous: n ? n.synchronous : !1,
      runWhen: n ? n.runWhen : null
    }), this.handlers.length - 1;
  }
  /**
   * Remove an interceptor from the stack
   *
   * @param {Number} id The ID that was returned by `use`
   *
   * @returns {Boolean} `true` if the interceptor was removed, `false` otherwise
   */
  eject(t) {
    this.handlers[t] && (this.handlers[t] = null);
  }
  /**
   * Clear all interceptors from the stack
   *
   * @returns {void}
   */
  clear() {
    this.handlers && (this.handlers = []);
  }
  /**
   * Iterate over all the registered interceptors
   *
   * This method is particularly useful for skipping over any
   * interceptors that may have become `null` calling `eject`.
   *
   * @param {Function} fn The function to call for each interceptor
   *
   * @returns {void}
   */
  forEach(t) {
    a.forEach(this.handlers, function(r) {
      r !== null && t(r);
    });
  }
}
const Je = {
  silentJSONParsing: !0,
  forcedJSONParsing: !0,
  clarifyTimeoutError: !1
}, Kt = typeof URLSearchParams < "u" ? URLSearchParams : de, $t = typeof FormData < "u" ? FormData : null, Gt = typeof Blob < "u" ? Blob : null, Xt = {
  isBrowser: !0,
  classes: {
    URLSearchParams: Kt,
    FormData: $t,
    Blob: Gt
  },
  protocols: ["http", "https", "file", "blob", "url", "data"]
}, pe = typeof window < "u" && typeof document < "u", Qt = ((e) => pe && ["ReactNative", "NativeScript", "NS"].indexOf(e) < 0)(typeof navigator < "u" && navigator.product), Zt = typeof WorkerGlobalScope < "u" && // eslint-disable-next-line no-undef
self instanceof WorkerGlobalScope && typeof self.importScripts == "function", Yt = pe && window.location.href || "http://localhost", er = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  hasBrowserEnv: pe,
  hasStandardBrowserEnv: Qt,
  hasStandardBrowserWebWorkerEnv: Zt,
  origin: Yt
}, Symbol.toStringTag, { value: "Module" })), A = {
  ...er,
  ...Xt
};
function tr(e, t) {
  return G(e, new A.classes.URLSearchParams(), Object.assign({
    visitor: function(r, n, o, s) {
      return A.isNode && a.isBuffer(r) ? (this.append(n, r.toString("base64")), !1) : s.defaultVisitor.apply(this, arguments);
    }
  }, t));
}
function rr(e) {
  return a.matchAll(/\w+|\[(\w*)]/g, e).map((t) => t[0] === "[]" ? "" : t[1] || t[0]);
}
function nr(e) {
  const t = {}, r = Object.keys(e);
  let n;
  const o = r.length;
  let s;
  for (n = 0; n < o; n++)
    s = r[n], t[s] = e[s];
  return t;
}
function We(e) {
  function t(r, n, o, s) {
    let i = r[s++];
    if (i === "__proto__") return !0;
    const c = Number.isFinite(+i), l = s >= r.length;
    return i = !i && a.isArray(o) ? o.length : i, l ? (a.hasOwnProp(o, i) ? o[i] = [o[i], n] : o[i] = n, !c) : ((!o[i] || !a.isObject(o[i])) && (o[i] = []), t(r, n, o[i], s) && a.isArray(o[i]) && (o[i] = nr(o[i])), !c);
  }
  if (a.isFormData(e) && a.isFunction(e.entries)) {
    const r = {};
    return a.forEachEntry(e, (n, o) => {
      t(rr(n), o, r, 0);
    }), r;
  }
  return null;
}
function or(e, t, r) {
  if (a.isString(e))
    try {
      return (t || JSON.parse)(e), a.trim(e);
    } catch (n) {
      if (n.name !== "SyntaxError")
        throw n;
    }
  return (0, JSON.stringify)(e);
}
const I = {
  transitional: Je,
  adapter: ["xhr", "http", "fetch"],
  transformRequest: [function(e, t) {
    const r = t.getContentType() || "", n = r.indexOf("application/json") > -1, o = a.isObject(e);
    if (o && a.isHTMLForm(e) && (e = new FormData(e)), a.isFormData(e))
      return n ? JSON.stringify(We(e)) : e;
    if (a.isArrayBuffer(e) || a.isBuffer(e) || a.isStream(e) || a.isFile(e) || a.isBlob(e) || a.isReadableStream(e))
      return e;
    if (a.isArrayBufferView(e))
      return e.buffer;
    if (a.isURLSearchParams(e))
      return t.setContentType("application/x-www-form-urlencoded;charset=utf-8", !1), e.toString();
    let s;
    if (o) {
      if (r.indexOf("application/x-www-form-urlencoded") > -1)
        return tr(e, this.formSerializer).toString();
      if ((s = a.isFileList(e)) || r.indexOf("multipart/form-data") > -1) {
        const i = this.env && this.env.FormData;
        return G(
          s ? { "files[]": e } : e,
          i && new i(),
          this.formSerializer
        );
      }
    }
    return o || n ? (t.setContentType("application/json", !1), or(e)) : e;
  }],
  transformResponse: [function(e) {
    const t = this.transitional || I.transitional, r = t && t.forcedJSONParsing, n = this.responseType === "json";
    if (a.isResponse(e) || a.isReadableStream(e))
      return e;
    if (e && a.isString(e) && (r && !this.responseType || n)) {
      const o = !(t && t.silentJSONParsing) && n;
      try {
        return JSON.parse(e);
      } catch (s) {
        if (o)
          throw s.name === "SyntaxError" ? g.from(s, g.ERR_BAD_RESPONSE, this, null, this.response) : s;
      }
    }
    return e;
  }],
  /**
   * A timeout in milliseconds to abort a request. If set to 0 (default) a
   * timeout is not created.
   */
  timeout: 0,
  xsrfCookieName: "XSRF-TOKEN",
  xsrfHeaderName: "X-XSRF-TOKEN",
  maxContentLength: -1,
  maxBodyLength: -1,
  env: {
    FormData: A.classes.FormData,
    Blob: A.classes.Blob
  },
  validateStatus: function(e) {
    return e >= 200 && e < 300;
  },
  headers: {
    common: {
      Accept: "application/json, text/plain, */*",
      "Content-Type": void 0
    }
  }
};
a.forEach(["delete", "get", "head", "post", "put", "patch"], (e) => {
  I.headers[e] = {};
});
const sr = a.toObjectSet([
  "age",
  "authorization",
  "content-length",
  "content-type",
  "etag",
  "expires",
  "from",
  "host",
  "if-modified-since",
  "if-unmodified-since",
  "last-modified",
  "location",
  "max-forwards",
  "proxy-authorization",
  "referer",
  "retry-after",
  "user-agent"
]), ir = (e) => {
  const t = {};
  let r, n, o;
  return e && e.split(`
`).forEach(function(s) {
    o = s.indexOf(":"), r = s.substring(0, o).trim().toLowerCase(), n = s.substring(o + 1).trim(), !(!r || t[r] && sr[r]) && (r === "set-cookie" ? t[r] ? t[r].push(n) : t[r] = [n] : t[r] = t[r] ? t[r] + ", " + n : n);
  }), t;
}, Se = Symbol("internals");
function U(e) {
  return e && String(e).trim().toLowerCase();
}
function H(e) {
  return e === !1 || e == null ? e : a.isArray(e) ? e.map(H) : String(e);
}
function ar(e) {
  const t = /* @__PURE__ */ Object.create(null), r = /([^\s,;=]+)\s*(?:=\s*([^,;]+))?/g;
  let n;
  for (; n = r.exec(e); )
    t[n[1]] = n[2];
  return t;
}
const cr = (e) => /^[-_a-zA-Z0-9^`|~,!#$%&'*+.]+$/.test(e.trim());
function Y(e, t, r, n, o) {
  if (a.isFunction(n))
    return n.call(this, t, r);
  if (o && (t = r), !!a.isString(t)) {
    if (a.isString(n))
      return t.indexOf(n) !== -1;
    if (a.isRegExp(n))
      return n.test(t);
  }
}
function ur(e) {
  return e.trim().toLowerCase().replace(/([a-z\d])(\w*)/g, (t, r, n) => r.toUpperCase() + n);
}
function lr(e, t) {
  const r = a.toCamelCase(" " + t);
  ["get", "set", "has"].forEach((n) => {
    Object.defineProperty(e, n + r, {
      value: function(o, s, i) {
        return this[n].call(this, t, o, s, i);
      },
      configurable: !0
    });
  });
}
class S {
  constructor(t) {
    t && this.set(t);
  }
  set(t, r, n) {
    const o = this;
    function s(c, l, u) {
      const f = U(l);
      if (!f)
        throw new Error("header name must be a non-empty string");
      const p = a.findKey(o, f);
      (!p || o[p] === void 0 || u === !0 || u === void 0 && o[p] !== !1) && (o[p || l] = H(c));
    }
    const i = (c, l) => a.forEach(c, (u, f) => s(u, f, l));
    if (a.isPlainObject(t) || t instanceof this.constructor)
      i(t, r);
    else if (a.isString(t) && (t = t.trim()) && !cr(t))
      i(ir(t), r);
    else if (a.isHeaders(t))
      for (const [c, l] of t.entries())
        s(l, c, n);
    else
      t != null && s(r, t, n);
    return this;
  }
  get(t, r) {
    if (t = U(t), t) {
      const n = a.findKey(this, t);
      if (n) {
        const o = this[n];
        if (!r)
          return o;
        if (r === !0)
          return ar(o);
        if (a.isFunction(r))
          return r.call(this, o, n);
        if (a.isRegExp(r))
          return r.exec(o);
        throw new TypeError("parser must be boolean|regexp|function");
      }
    }
  }
  has(t, r) {
    if (t = U(t), t) {
      const n = a.findKey(this, t);
      return !!(n && this[n] !== void 0 && (!r || Y(this, this[n], n, r)));
    }
    return !1;
  }
  delete(t, r) {
    const n = this;
    let o = !1;
    function s(i) {
      if (i = U(i), i) {
        const c = a.findKey(n, i);
        c && (!r || Y(n, n[c], c, r)) && (delete n[c], o = !0);
      }
    }
    return a.isArray(t) ? t.forEach(s) : s(t), o;
  }
  clear(t) {
    const r = Object.keys(this);
    let n = r.length, o = !1;
    for (; n--; ) {
      const s = r[n];
      (!t || Y(this, this[s], s, t, !0)) && (delete this[s], o = !0);
    }
    return o;
  }
  normalize(t) {
    const r = this, n = {};
    return a.forEach(this, (o, s) => {
      const i = a.findKey(n, s);
      if (i) {
        r[i] = H(o), delete r[s];
        return;
      }
      const c = t ? ur(s) : String(s).trim();
      c !== s && delete r[s], r[c] = H(o), n[c] = !0;
    }), this;
  }
  concat(...t) {
    return this.constructor.concat(this, ...t);
  }
  toJSON(t) {
    const r = /* @__PURE__ */ Object.create(null);
    return a.forEach(this, (n, o) => {
      n != null && n !== !1 && (r[o] = t && a.isArray(n) ? n.join(", ") : n);
    }), r;
  }
  [Symbol.iterator]() {
    return Object.entries(this.toJSON())[Symbol.iterator]();
  }
  toString() {
    return Object.entries(this.toJSON()).map(([t, r]) => t + ": " + r).join(`
`);
  }
  get [Symbol.toStringTag]() {
    return "AxiosHeaders";
  }
  static from(t) {
    return t instanceof this ? t : new this(t);
  }
  static concat(t, ...r) {
    const n = new this(t);
    return r.forEach((o) => n.set(o)), n;
  }
  static accessor(t) {
    const r = (this[Se] = this[Se] = {
      accessors: {}
    }).accessors, n = this.prototype;
    function o(s) {
      const i = U(s);
      r[i] || (lr(n, s), r[i] = !0);
    }
    return a.isArray(t) ? t.forEach(o) : o(t), this;
  }
}
S.accessor(["Content-Type", "Content-Length", "Accept", "Accept-Encoding", "User-Agent", "Authorization"]);
a.reduceDescriptors(S.prototype, ({ value: e }, t) => {
  let r = t[0].toUpperCase() + t.slice(1);
  return {
    get: () => e,
    set(n) {
      this[r] = n;
    }
  };
});
a.freezeMethods(S);
function ee(e, t) {
  const r = this || I, n = t || r, o = S.from(n.headers);
  let s = n.data;
  return a.forEach(e, function(i) {
    s = i.call(r, s, o.normalize(), t ? t.status : void 0);
  }), o.normalize(), s;
}
function Ve(e) {
  return !!(e && e.__CANCEL__);
}
function F(e, t, r) {
  g.call(this, e ?? "canceled", g.ERR_CANCELED, t, r), this.name = "CanceledError";
}
a.inherits(F, g, {
  __CANCEL__: !0
});
function Ke(e, t, r) {
  const n = r.config.validateStatus;
  !r.status || !n || n(r.status) ? e(r) : t(new g(
    "Request failed with status code " + r.status,
    [g.ERR_BAD_REQUEST, g.ERR_BAD_RESPONSE][Math.floor(r.status / 100) - 4],
    r.config,
    r.request,
    r
  ));
}
function fr(e) {
  const t = /^([-+\w]{1,25})(:?\/\/|:)/.exec(e);
  return t && t[1] || "";
}
function dr(e, t) {
  e = e || 10;
  const r = new Array(e), n = new Array(e);
  let o = 0, s = 0, i;
  return t = t !== void 0 ? t : 1e3, function(c) {
    const l = Date.now(), u = n[s];
    i || (i = l), r[o] = c, n[o] = l;
    let f = s, p = 0;
    for (; f !== o; )
      p += r[f++], f = f % e;
    if (o = (o + 1) % e, o === s && (s = (s + 1) % e), l - i < t)
      return;
    const y = u && l - u;
    return y ? Math.round(p * 1e3 / y) : void 0;
  };
}
function pr(e, t) {
  let r = 0, n = 1e3 / t, o, s;
  const i = (c, l = Date.now()) => {
    r = l, o = null, s && (clearTimeout(s), s = null), e.apply(null, c);
  };
  return [(...c) => {
    const l = Date.now(), u = l - r;
    u >= n ? i(c, l) : (o = c, s || (s = setTimeout(() => {
      s = null, i(o);
    }, n - u)));
  }, () => o && i(o)];
}
const J = (e, t, r = 3) => {
  let n = 0;
  const o = dr(50, 250);
  return pr((s) => {
    const i = s.loaded, c = s.lengthComputable ? s.total : void 0, l = i - n, u = o(l), f = i <= c;
    n = i;
    const p = {
      loaded: i,
      total: c,
      progress: c ? i / c : void 0,
      bytes: l,
      rate: u || void 0,
      estimated: u && c && f ? (c - i) / u : void 0,
      event: s,
      lengthComputable: c != null,
      [t ? "download" : "upload"]: !0
    };
    e(p);
  }, r);
}, Oe = (e, t) => {
  const r = e != null;
  return [(n) => t[0]({
    lengthComputable: r,
    total: e,
    loaded: n
  }), t[1]];
}, Te = (e) => (...t) => a.asap(() => e(...t)), hr = A.hasStandardBrowserEnv ? (
  // Standard browser envs have full support of the APIs needed to test
  // whether the request URL is of the same origin as current location.
  function() {
    const e = /(msie|trident)/i.test(navigator.userAgent), t = document.createElement("a");
    let r;
    function n(o) {
      let s = o;
      return e && (t.setAttribute("href", s), s = t.href), t.setAttribute("href", s), {
        href: t.href,
        protocol: t.protocol ? t.protocol.replace(/:$/, "") : "",
        host: t.host,
        search: t.search ? t.search.replace(/^\?/, "") : "",
        hash: t.hash ? t.hash.replace(/^#/, "") : "",
        hostname: t.hostname,
        port: t.port,
        pathname: t.pathname.charAt(0) === "/" ? t.pathname : "/" + t.pathname
      };
    }
    return r = n(window.location.href), function(o) {
      const s = a.isString(o) ? n(o) : o;
      return s.protocol === r.protocol && s.host === r.host;
    };
  }()
) : (
  // Non standard browser envs (web workers, react-native) lack needed support.
  /* @__PURE__ */ function() {
    return function() {
      return !0;
    };
  }()
), mr = A.hasStandardBrowserEnv ? (
  // Standard browser envs support document.cookie
  {
    write(e, t, r, n, o, s) {
      const i = [e + "=" + encodeURIComponent(t)];
      a.isNumber(r) && i.push("expires=" + new Date(r).toGMTString()), a.isString(n) && i.push("path=" + n), a.isString(o) && i.push("domain=" + o), s === !0 && i.push("secure"), document.cookie = i.join("; ");
    },
    read(e) {
      const t = document.cookie.match(new RegExp("(^|;\\s*)(" + e + ")=([^;]*)"));
      return t ? decodeURIComponent(t[3]) : null;
    },
    remove(e) {
      this.write(e, "", Date.now() - 864e5);
    }
  }
) : (
  // Non-standard browser env (web workers, react-native) lack needed support.
  {
    write() {
    },
    read() {
      return null;
    },
    remove() {
    }
  }
);
function gr(e) {
  return /^([a-z][a-z\d+\-.]*:)?\/\//i.test(e);
}
function br(e, t) {
  return t ? e.replace(/\/?\/$/, "") + "/" + t.replace(/^\/+/, "") : e;
}
function $e(e, t) {
  return e && !gr(t) ? br(e, t) : t;
}
const Ae = (e) => e instanceof S ? { ...e } : e;
function P(e, t) {
  t = t || {};
  const r = {};
  function n(u, f, p) {
    return a.isPlainObject(u) && a.isPlainObject(f) ? a.merge.call({ caseless: p }, u, f) : a.isPlainObject(f) ? a.merge({}, f) : a.isArray(f) ? f.slice() : f;
  }
  function o(u, f, p) {
    if (a.isUndefined(f)) {
      if (!a.isUndefined(u))
        return n(void 0, u, p);
    } else return n(u, f, p);
  }
  function s(u, f) {
    if (!a.isUndefined(f))
      return n(void 0, f);
  }
  function i(u, f) {
    if (a.isUndefined(f)) {
      if (!a.isUndefined(u))
        return n(void 0, u);
    } else return n(void 0, f);
  }
  function c(u, f, p) {
    if (p in t)
      return n(u, f);
    if (p in e)
      return n(void 0, u);
  }
  const l = {
    url: s,
    method: s,
    data: s,
    baseURL: i,
    transformRequest: i,
    transformResponse: i,
    paramsSerializer: i,
    timeout: i,
    timeoutMessage: i,
    withCredentials: i,
    withXSRFToken: i,
    adapter: i,
    responseType: i,
    xsrfCookieName: i,
    xsrfHeaderName: i,
    onUploadProgress: i,
    onDownloadProgress: i,
    decompress: i,
    maxContentLength: i,
    maxBodyLength: i,
    beforeRedirect: i,
    transport: i,
    httpAgent: i,
    httpsAgent: i,
    cancelToken: i,
    socketPath: i,
    responseEncoding: i,
    validateStatus: c,
    headers: (u, f) => o(Ae(u), Ae(f), !0)
  };
  return a.forEach(Object.keys(Object.assign({}, e, t)), function(u) {
    const f = l[u] || o, p = f(e[u], t[u], u);
    a.isUndefined(p) && f !== c || (r[u] = p);
  }), r;
}
const Ge = (e) => {
  const t = P({}, e);
  let { data: r, withXSRFToken: n, xsrfHeaderName: o, xsrfCookieName: s, headers: i, auth: c } = t;
  t.headers = i = S.from(i), t.url = He($e(t.baseURL, t.url), e.params, e.paramsSerializer), c && i.set(
    "Authorization",
    "Basic " + btoa((c.username || "") + ":" + (c.password ? unescape(encodeURIComponent(c.password)) : ""))
  );
  let l;
  if (a.isFormData(r)) {
    if (A.hasStandardBrowserEnv || A.hasStandardBrowserWebWorkerEnv)
      i.setContentType(void 0);
    else if ((l = i.getContentType()) !== !1) {
      const [u, ...f] = l ? l.split(";").map((p) => p.trim()).filter(Boolean) : [];
      i.setContentType([u || "multipart/form-data", ...f].join("; "));
    }
  }
  if (A.hasStandardBrowserEnv && (n && a.isFunction(n) && (n = n(t)), n || n !== !1 && hr(t.url))) {
    const u = o && s && mr.read(s);
    u && i.set(o, u);
  }
  return t;
}, yr = typeof XMLHttpRequest < "u", wr = yr && function(e) {
  return new Promise(function(t, r) {
    const n = Ge(e);
    let o = n.data;
    const s = S.from(n.headers).normalize();
    let { responseType: i, onUploadProgress: c, onDownloadProgress: l } = n, u, f, p, y, m;
    function h() {
      y && y(), m && m(), n.cancelToken && n.cancelToken.unsubscribe(u), n.signal && n.signal.removeEventListener("abort", u);
    }
    let d = new XMLHttpRequest();
    d.open(n.method.toUpperCase(), n.url, !0), d.timeout = n.timeout;
    function E() {
      if (!d)
        return;
      const R = S.from(
        "getAllResponseHeaders" in d && d.getAllResponseHeaders()
      ), b = {
        data: !i || i === "text" || i === "json" ? d.responseText : d.response,
        status: d.status,
        statusText: d.statusText,
        headers: R,
        config: e,
        request: d
      };
      Ke(function(j) {
        t(j), h();
      }, function(j) {
        r(j), h();
      }, b), d = null;
    }
    "onloadend" in d ? d.onloadend = E : d.onreadystatechange = function() {
      !d || d.readyState !== 4 || d.status === 0 && !(d.responseURL && d.responseURL.indexOf("file:") === 0) || setTimeout(E);
    }, d.onabort = function() {
      d && (r(new g("Request aborted", g.ECONNABORTED, e, d)), d = null);
    }, d.onerror = function() {
      r(new g("Network Error", g.ERR_NETWORK, e, d)), d = null;
    }, d.ontimeout = function() {
      let R = n.timeout ? "timeout of " + n.timeout + "ms exceeded" : "timeout exceeded";
      const b = n.transitional || Je;
      n.timeoutErrorMessage && (R = n.timeoutErrorMessage), r(new g(
        R,
        b.clarifyTimeoutError ? g.ETIMEDOUT : g.ECONNABORTED,
        e,
        d
      )), d = null;
    }, o === void 0 && s.setContentType(null), "setRequestHeader" in d && a.forEach(s.toJSON(), function(R, b) {
      d.setRequestHeader(b, R);
    }), a.isUndefined(n.withCredentials) || (d.withCredentials = !!n.withCredentials), i && i !== "json" && (d.responseType = n.responseType), l && ([p, m] = J(l, !0), d.addEventListener("progress", p)), c && d.upload && ([f, y] = J(c), d.upload.addEventListener("progress", f), d.upload.addEventListener("loadend", y)), (n.cancelToken || n.signal) && (u = (R) => {
      d && (r(!R || R.type ? new F(null, e, d) : R), d.abort(), d = null);
    }, n.cancelToken && n.cancelToken.subscribe(u), n.signal && (n.signal.aborted ? u() : n.signal.addEventListener("abort", u)));
    const T = fr(n.url);
    if (T && A.protocols.indexOf(T) === -1) {
      r(new g("Unsupported protocol " + T + ":", g.ERR_BAD_REQUEST, e));
      return;
    }
    d.send(o || null);
  });
}, Er = (e, t) => {
  let r = new AbortController(), n;
  const o = function(l) {
    if (!n) {
      n = !0, i();
      const u = l instanceof Error ? l : this.reason;
      r.abort(u instanceof g ? u : new F(u instanceof Error ? u.message : u));
    }
  };
  let s = t && setTimeout(() => {
    o(new g(`timeout ${t} of ms exceeded`, g.ETIMEDOUT));
  }, t);
  const i = () => {
    e && (s && clearTimeout(s), s = null, e.forEach((l) => {
      l && (l.removeEventListener ? l.removeEventListener("abort", o) : l.unsubscribe(o));
    }), e = null);
  };
  e.forEach((l) => l && l.addEventListener && l.addEventListener("abort", o));
  const { signal: c } = r;
  return c.unsubscribe = i, [c, () => {
    s && clearTimeout(s), s = null;
  }];
}, Rr = function* (e, t) {
  let r = e.byteLength;
  if (r < t) {
    yield e;
    return;
  }
  let n = 0, o;
  for (; n < r; )
    o = n + t, yield e.slice(n, o), n = o;
}, Sr = async function* (e, t, r) {
  for await (const n of e)
    yield* Rr(ArrayBuffer.isView(n) ? n : await r(String(n)), t);
}, xe = (e, t, r, n, o) => {
  const s = Sr(e, t, o);
  let i = 0, c, l = (u) => {
    c || (c = !0, n && n(u));
  };
  return new ReadableStream({
    async pull(u) {
      try {
        const { done: f, value: p } = await s.next();
        if (f) {
          l(), u.close();
          return;
        }
        let y = p.byteLength;
        if (r) {
          let m = i += y;
          r(m);
        }
        u.enqueue(new Uint8Array(p));
      } catch (f) {
        throw l(f), f;
      }
    },
    cancel(u) {
      return l(u), s.return();
    }
  }, {
    highWaterMark: 2
  });
}, X = typeof fetch == "function" && typeof Request == "function" && typeof Response == "function", Xe = X && typeof ReadableStream == "function", se = X && (typeof TextEncoder == "function" ? /* @__PURE__ */ ((e) => (t) => e.encode(t))(new TextEncoder()) : async (e) => new Uint8Array(await new Response(e).arrayBuffer())), Qe = (e, ...t) => {
  try {
    return !!e(...t);
  } catch {
    return !1;
  }
}, Or = Xe && Qe(() => {
  let e = !1;
  const t = new Request(A.origin, {
    body: new ReadableStream(),
    method: "POST",
    get duplex() {
      return e = !0, "half";
    }
  }).headers.has("Content-Type");
  return e && !t;
}), ve = 64 * 1024, ie = Xe && Qe(() => a.isReadableStream(new Response("").body)), W = {
  stream: ie && ((e) => e.body)
};
X && ((e) => {
  ["text", "arrayBuffer", "blob", "formData", "stream"].forEach((t) => {
    !W[t] && (W[t] = a.isFunction(e[t]) ? (r) => r[t]() : (r, n) => {
      throw new g(`Response type '${t}' is not supported`, g.ERR_NOT_SUPPORT, n);
    });
  });
})(new Response());
const Tr = async (e) => {
  if (e == null)
    return 0;
  if (a.isBlob(e))
    return e.size;
  if (a.isSpecCompliantForm(e))
    return (await new Request(e).arrayBuffer()).byteLength;
  if (a.isArrayBufferView(e) || a.isArrayBuffer(e))
    return e.byteLength;
  if (a.isURLSearchParams(e) && (e = e + ""), a.isString(e))
    return (await se(e)).byteLength;
}, Ar = async (e, t) => a.toFiniteNumber(e.getContentLength()) ?? Tr(t), xr = X && (async (e) => {
  let {
    url: t,
    method: r,
    data: n,
    signal: o,
    cancelToken: s,
    timeout: i,
    onDownloadProgress: c,
    onUploadProgress: l,
    responseType: u,
    headers: f,
    withCredentials: p = "same-origin",
    fetchOptions: y
  } = Ge(e);
  u = u ? (u + "").toLowerCase() : "text";
  let [m, h] = o || s || i ? Er([o, s], i) : [], d, E;
  const T = () => {
    !d && setTimeout(() => {
      m && m.unsubscribe();
    }), d = !0;
  };
  let R;
  try {
    if (l && Or && r !== "get" && r !== "head" && (R = await Ar(f, n)) !== 0) {
      let v = new Request(t, {
        method: "POST",
        body: n,
        duplex: "half"
      }), L;
      if (a.isFormData(n) && (L = v.headers.get("content-type")) && f.setContentType(L), v.body) {
        const [Q, M] = Oe(
          R,
          J(Te(l))
        );
        n = xe(v.body, ve, Q, M, se);
      }
    }
    a.isString(p) || (p = p ? "include" : "omit"), E = new Request(t, {
      ...y,
      signal: m,
      method: r.toUpperCase(),
      headers: f.normalize().toJSON(),
      body: n,
      duplex: "half",
      credentials: p
    });
    let b = await fetch(E);
    const j = ie && (u === "stream" || u === "response");
    if (ie && (c || j)) {
      const v = {};
      ["status", "statusText", "headers"].forEach((ge) => {
        v[ge] = b[ge];
      });
      const L = a.toFiniteNumber(b.headers.get("content-length")), [Q, M] = c && Oe(
        L,
        J(Te(c), !0)
      ) || [];
      b = new Response(
        xe(b.body, ve, Q, () => {
          M && M(), j && T();
        }, se),
        v
      );
    }
    u = u || "text";
    let rt = await W[a.findKey(W, u) || "text"](b, e);
    return !j && T(), h && h(), await new Promise((v, L) => {
      Ke(v, L, {
        data: rt,
        headers: S.from(b.headers),
        status: b.status,
        statusText: b.statusText,
        config: e,
        request: E
      });
    });
  } catch (b) {
    throw T(), b && b.name === "TypeError" && /fetch/i.test(b.message) ? Object.assign(
      new g("Network Error", g.ERR_NETWORK, e, E),
      {
        cause: b.cause || b
      }
    ) : g.from(b, b && b.code, e, E);
  }
}), ae = {
  http: Ht,
  xhr: wr,
  fetch: xr
};
a.forEach(ae, (e, t) => {
  if (e) {
    try {
      Object.defineProperty(e, "name", { value: t });
    } catch {
    }
    Object.defineProperty(e, "adapterName", { value: t });
  }
});
const Ce = (e) => `- ${e}`, vr = (e) => a.isFunction(e) || e === null || e === !1, Ze = {
  getAdapter: (e) => {
    e = a.isArray(e) ? e : [e];
    const { length: t } = e;
    let r, n;
    const o = {};
    for (let s = 0; s < t; s++) {
      r = e[s];
      let i;
      if (n = r, !vr(r) && (n = ae[(i = String(r)).toLowerCase()], n === void 0))
        throw new g(`Unknown adapter '${i}'`);
      if (n)
        break;
      o[i || "#" + s] = n;
    }
    if (!n) {
      const s = Object.entries(o).map(
        ([c, l]) => `adapter ${c} ` + (l === !1 ? "is not supported by the environment" : "is not available in the build")
      );
      let i = t ? s.length > 1 ? `since :
` + s.map(Ce).join(`
`) : " " + Ce(s[0]) : "as no adapter specified";
      throw new g(
        "There is no suitable adapter to dispatch the request " + i,
        "ERR_NOT_SUPPORT"
      );
    }
    return n;
  },
  adapters: ae
};
function te(e) {
  if (e.cancelToken && e.cancelToken.throwIfRequested(), e.signal && e.signal.aborted)
    throw new F(null, e);
}
function je(e) {
  return te(e), e.headers = S.from(e.headers), e.data = ee.call(
    e,
    e.transformRequest
  ), ["post", "put", "patch"].indexOf(e.method) !== -1 && e.headers.setContentType("application/x-www-form-urlencoded", !1), Ze.getAdapter(e.adapter || I.adapter)(e).then(function(t) {
    return te(e), t.data = ee.call(
      e,
      e.transformResponse,
      t
    ), t.headers = S.from(t.headers), t;
  }, function(t) {
    return Ve(t) || (te(e), t && t.response && (t.response.data = ee.call(
      e,
      e.transformResponse,
      t.response
    ), t.response.headers = S.from(t.response.headers))), Promise.reject(t);
  });
}
const Ye = "1.7.3", he = {};
["object", "boolean", "number", "function", "string", "symbol"].forEach((e, t) => {
  he[e] = function(r) {
    return typeof r === e || "a" + (t < 1 ? "n " : " ") + e;
  };
});
const Ne = {};
he.transitional = function(e, t, r) {
  function n(o, s) {
    return "[Axios v" + Ye + "] Transitional option '" + o + "'" + s + (r ? ". " + r : "");
  }
  return (o, s, i) => {
    if (e === !1)
      throw new g(
        n(s, " has been removed" + (t ? " in " + t : "")),
        g.ERR_DEPRECATED
      );
    return t && !Ne[s] && (Ne[s] = !0, console.warn(
      n(
        s,
        " has been deprecated since v" + t + " and will be removed in the near future"
      )
    )), e ? e(o, s, i) : !0;
  };
};
function Cr(e, t, r) {
  if (typeof e != "object")
    throw new g("options must be an object", g.ERR_BAD_OPTION_VALUE);
  const n = Object.keys(e);
  let o = n.length;
  for (; o-- > 0; ) {
    const s = n[o], i = t[s];
    if (i) {
      const c = e[s], l = c === void 0 || i(c, s, e);
      if (l !== !0)
        throw new g("option " + s + " must be " + l, g.ERR_BAD_OPTION_VALUE);
      continue;
    }
    if (r !== !0)
      throw new g("Unknown option " + s, g.ERR_BAD_OPTION);
  }
}
const ce = {
  assertOptions: Cr,
  validators: he
}, C = ce.validators;
class _ {
  constructor(t) {
    this.defaults = t, this.interceptors = {
      request: new Re(),
      response: new Re()
    };
  }
  /**
   * Dispatch a request
   *
   * @param {String|Object} configOrUrl The config specific for this request (merged with this.defaults)
   * @param {?Object} config
   *
   * @returns {Promise} The Promise to be fulfilled
   */
  async request(t, r) {
    try {
      return await this._request(t, r);
    } catch (n) {
      if (n instanceof Error) {
        let o;
        Error.captureStackTrace ? Error.captureStackTrace(o = {}) : o = new Error();
        const s = o.stack ? o.stack.replace(/^.+\n/, "") : "";
        try {
          n.stack ? s && !String(n.stack).endsWith(s.replace(/^.+\n.+\n/, "")) && (n.stack += `
` + s) : n.stack = s;
        } catch {
        }
      }
      throw n;
    }
  }
  _request(t, r) {
    typeof t == "string" ? (r = r || {}, r.url = t) : r = t || {}, r = P(this.defaults, r);
    const { transitional: n, paramsSerializer: o, headers: s } = r;
    n !== void 0 && ce.assertOptions(n, {
      silentJSONParsing: C.transitional(C.boolean),
      forcedJSONParsing: C.transitional(C.boolean),
      clarifyTimeoutError: C.transitional(C.boolean)
    }, !1), o != null && (a.isFunction(o) ? r.paramsSerializer = {
      serialize: o
    } : ce.assertOptions(o, {
      encode: C.function,
      serialize: C.function
    }, !0)), r.method = (r.method || this.defaults.method || "get").toLowerCase();
    let i = s && a.merge(
      s.common,
      s[r.method]
    );
    s && a.forEach(
      ["delete", "get", "head", "post", "put", "patch", "common"],
      (h) => {
        delete s[h];
      }
    ), r.headers = S.concat(i, s);
    const c = [];
    let l = !0;
    this.interceptors.request.forEach(function(h) {
      typeof h.runWhen == "function" && h.runWhen(r) === !1 || (l = l && h.synchronous, c.unshift(h.fulfilled, h.rejected));
    });
    const u = [];
    this.interceptors.response.forEach(function(h) {
      u.push(h.fulfilled, h.rejected);
    });
    let f, p = 0, y;
    if (!l) {
      const h = [je.bind(this), void 0];
      for (h.unshift.apply(h, c), h.push.apply(h, u), y = h.length, f = Promise.resolve(r); p < y; )
        f = f.then(h[p++], h[p++]);
      return f;
    }
    y = c.length;
    let m = r;
    for (p = 0; p < y; ) {
      const h = c[p++], d = c[p++];
      try {
        m = h(m);
      } catch (E) {
        d.call(this, E);
        break;
      }
    }
    try {
      f = je.call(this, m);
    } catch (h) {
      return Promise.reject(h);
    }
    for (p = 0, y = u.length; p < y; )
      f = f.then(u[p++], u[p++]);
    return f;
  }
  getUri(t) {
    t = P(this.defaults, t);
    const r = $e(t.baseURL, t.url);
    return He(r, t.params, t.paramsSerializer);
  }
}
a.forEach(["delete", "get", "head", "options"], function(e) {
  _.prototype[e] = function(t, r) {
    return this.request(P(r || {}, {
      method: e,
      url: t,
      data: (r || {}).data
    }));
  };
});
a.forEach(["post", "put", "patch"], function(e) {
  function t(r) {
    return function(n, o, s) {
      return this.request(P(s || {}, {
        method: e,
        headers: r ? {
          "Content-Type": "multipart/form-data"
        } : {},
        url: n,
        data: o
      }));
    };
  }
  _.prototype[e] = t(), _.prototype[e + "Form"] = t(!0);
});
class me {
  constructor(t) {
    if (typeof t != "function")
      throw new TypeError("executor must be a function.");
    let r;
    this.promise = new Promise(function(o) {
      r = o;
    });
    const n = this;
    this.promise.then((o) => {
      if (!n._listeners) return;
      let s = n._listeners.length;
      for (; s-- > 0; )
        n._listeners[s](o);
      n._listeners = null;
    }), this.promise.then = (o) => {
      let s;
      const i = new Promise((c) => {
        n.subscribe(c), s = c;
      }).then(o);
      return i.cancel = function() {
        n.unsubscribe(s);
      }, i;
    }, t(function(o, s, i) {
      n.reason || (n.reason = new F(o, s, i), r(n.reason));
    });
  }
  /**
   * Throws a `CanceledError` if cancellation has been requested.
   */
  throwIfRequested() {
    if (this.reason)
      throw this.reason;
  }
  /**
   * Subscribe to the cancel signal
   */
  subscribe(t) {
    if (this.reason) {
      t(this.reason);
      return;
    }
    this._listeners ? this._listeners.push(t) : this._listeners = [t];
  }
  /**
   * Unsubscribe from the cancel signal
   */
  unsubscribe(t) {
    if (!this._listeners)
      return;
    const r = this._listeners.indexOf(t);
    r !== -1 && this._listeners.splice(r, 1);
  }
  /**
   * Returns an object that contains a new `CancelToken` and a function that, when called,
   * cancels the `CancelToken`.
   */
  static source() {
    let t;
    return {
      token: new me(function(r) {
        t = r;
      }),
      cancel: t
    };
  }
}
function jr(e) {
  return function(t) {
    return e.apply(null, t);
  };
}
function Nr(e) {
  return a.isObject(e) && e.isAxiosError === !0;
}
const ue = {
  Continue: 100,
  SwitchingProtocols: 101,
  Processing: 102,
  EarlyHints: 103,
  Ok: 200,
  Created: 201,
  Accepted: 202,
  NonAuthoritativeInformation: 203,
  NoContent: 204,
  ResetContent: 205,
  PartialContent: 206,
  MultiStatus: 207,
  AlreadyReported: 208,
  ImUsed: 226,
  MultipleChoices: 300,
  MovedPermanently: 301,
  Found: 302,
  SeeOther: 303,
  NotModified: 304,
  UseProxy: 305,
  Unused: 306,
  TemporaryRedirect: 307,
  PermanentRedirect: 308,
  BadRequest: 400,
  Unauthorized: 401,
  PaymentRequired: 402,
  Forbidden: 403,
  NotFound: 404,
  MethodNotAllowed: 405,
  NotAcceptable: 406,
  ProxyAuthenticationRequired: 407,
  RequestTimeout: 408,
  Conflict: 409,
  Gone: 410,
  LengthRequired: 411,
  PreconditionFailed: 412,
  PayloadTooLarge: 413,
  UriTooLong: 414,
  UnsupportedMediaType: 415,
  RangeNotSatisfiable: 416,
  ExpectationFailed: 417,
  ImATeapot: 418,
  MisdirectedRequest: 421,
  UnprocessableEntity: 422,
  Locked: 423,
  FailedDependency: 424,
  TooEarly: 425,
  UpgradeRequired: 426,
  PreconditionRequired: 428,
  TooManyRequests: 429,
  RequestHeaderFieldsTooLarge: 431,
  UnavailableForLegalReasons: 451,
  InternalServerError: 500,
  NotImplemented: 501,
  BadGateway: 502,
  ServiceUnavailable: 503,
  GatewayTimeout: 504,
  HttpVersionNotSupported: 505,
  VariantAlsoNegotiates: 506,
  InsufficientStorage: 507,
  LoopDetected: 508,
  NotExtended: 510,
  NetworkAuthenticationRequired: 511
};
Object.entries(ue).forEach(([e, t]) => {
  ue[t] = e;
});
function et(e) {
  const t = new _(e), r = _e(_.prototype.request, t);
  return a.extend(r, _.prototype, t, { allOwnKeys: !0 }), a.extend(r, t, null, { allOwnKeys: !0 }), r.create = function(n) {
    return et(P(e, n));
  }, r;
}
const w = et(I);
w.Axios = _;
w.CanceledError = F;
w.CancelToken = me;
w.isCancel = Ve;
w.VERSION = Ye;
w.toFormData = G;
w.AxiosError = g;
w.Cancel = w.CanceledError;
w.all = function(e) {
  return Promise.all(e);
};
w.spread = jr;
w.isAxiosError = Nr;
w.mergeConfig = P;
w.AxiosHeaders = S;
w.formToJSON = (e) => We(a.isHTMLForm(e) ? new FormData(e) : e);
w.getAdapter = Ze.getAdapter;
w.HttpStatusCode = ue;
w.default = w;
const _r = (e) => {
  const t = typeof e;
  return e !== null && (t === "object" || t === "function");
}, re = /* @__PURE__ */ new Set([
  "__proto__",
  "prototype",
  "constructor"
]), Pr = new Set("0123456789");
function Lr(e) {
  const t = [];
  let r = "", n = "start", o = !1;
  for (const s of e)
    switch (s) {
      case "\\": {
        if (n === "index")
          throw new Error("Invalid character in an index");
        if (n === "indexEnd")
          throw new Error("Invalid character after an index");
        o && (r += s), n = "property", o = !o;
        break;
      }
      case ".": {
        if (n === "index")
          throw new Error("Invalid character in an index");
        if (n === "indexEnd") {
          n = "property";
          break;
        }
        if (o) {
          o = !1, r += s;
          break;
        }
        if (re.has(r))
          return [];
        t.push(r), r = "", n = "property";
        break;
      }
      case "[": {
        if (n === "index")
          throw new Error("Invalid character in an index");
        if (n === "indexEnd") {
          n = "index";
          break;
        }
        if (o) {
          o = !1, r += s;
          break;
        }
        if (n === "property") {
          if (re.has(r))
            return [];
          t.push(r), r = "";
        }
        n = "index";
        break;
      }
      case "]": {
        if (n === "index") {
          t.push(Number.parseInt(r, 10)), r = "", n = "indexEnd";
          break;
        }
        if (n === "indexEnd")
          throw new Error("Invalid character after an index");
      }
      default: {
        if (n === "index" && !Pr.has(s))
          throw new Error("Invalid character in an index");
        if (n === "indexEnd")
          throw new Error("Invalid character after an index");
        n === "start" && (n = "property"), o && (o = !1, r += "\\"), r += s;
      }
    }
  switch (o && (r += "\\"), n) {
    case "property": {
      if (re.has(r))
        return [];
      t.push(r);
      break;
    }
    case "index":
      throw new Error("Index was not closed");
    case "start": {
      t.push("");
      break;
    }
  }
  return t;
}
function Br(e, t) {
  if (typeof t != "number" && Array.isArray(e)) {
    const r = Number.parseInt(t, 10);
    return Number.isInteger(r) && e[r] === e[t];
  }
  return !1;
}
function Fr(e, t, r) {
  if (!_r(e) || typeof t != "string")
    return r === void 0 ? e : r;
  const n = Lr(t);
  if (n.length === 0)
    return r;
  for (let o = 0; o < n.length; o++) {
    const s = n[o];
    if (Br(e, s) ? e = o === n.length - 1 ? void 0 : null : e = e[s], e == null) {
      if (o !== n.length - 1)
        return r;
      break;
    }
  }
  return e === void 0 ? r : e;
}
const tt = nt("config", {
  persist: !0,
  state: () => ({
    config: {}
  }),
  getters: {
    get: (e) => (t, r) => Fr(e.config, t, r)
  },
  actions: {
    async load() {
      w.get("/api/config").then((e) => {
        this.config = e.data;
      });
    }
  }
});
function Ur() {
  return {
    first_name: "",
    last_name: "",
    email: "",
    user_name: "",
    password: "",
    passwordc: "",
    locale: tt().get("site.registration.user_defaults.locale", "en_US"),
    captcha: "",
    spiderbro: "http://"
  };
}
function kr() {
  return tt().get("locales.available");
}
function Dr() {
  return "/account/captcha";
}
async function qr(e) {
  return le.post("/account/register", e).then((t) => t.data).catch((t) => {
    throw {
      description: "An error as occurred",
      style: k.Danger,
      closeBtn: !0,
      ...t.response.data
    };
  });
}
const zr = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  doRegister: qr,
  getAvailableLocales: kr,
  getCaptchaUrl: Dr,
  getDefaultForm: Ur
}, Symbol.toStringTag, { value: "Module" }));
async function Hr(e) {
  return le.post("/account/forgot-password", { email: e }).then((t) => ({
    description: t.data.message,
    style: k.Success,
    closeBtn: !0
  })).catch((t) => {
    throw {
      description: "An error as occurred",
      style: k.Danger,
      closeBtn: !0,
      ...t.response.data
    };
  });
}
async function Jr(e) {
  return le.post("/account/resend-verification", { email: e }).then((t) => ({
    description: t.data.message,
    style: k.Success,
    closeBtn: !0
  })).catch((t) => {
    throw {
      description: "An error as occurred",
      style: k.Danger,
      closeBtn: !0,
      ...t.response.data
    };
  });
}
export {
  zr as Register,
  Hr as forgotPassword,
  Jr as resendVerification
};
