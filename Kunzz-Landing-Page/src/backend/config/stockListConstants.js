export const SYSTEM_OPTIONS = [
  { value: 'central', label: '中央' },
  { value: 'j1', label: 'J1' },
  { value: 'j2', label: 'J2' },
  { value: 'j3', label: 'J3' },
];

export const VIEW_OPTIONS = [
  { value: 'list', label: '总库存' },
  { value: 'records', label: '进出货' },
  { value: 'remark', label: '货品备注' },
  { value: 'product', label: '货品种类' },
  { value: 'sot', label: '货品异常' },
];

export const VIEW_NAMES = {
  list: '总库存',
  records: '进出货',
  remark: '货品备注',
  product: '货品种类',
  sot: '货品异常',
};

export const SYSTEM_NAMES = {
  central: '中央',
  j1: 'J1',
  j2: 'J2',
  j3: 'J3',
};

export const PAGE_TITLES = {
  central: '总库存 - 中央',
  j1: '总库存 - J1',
  j2: '总库存 - J2',
  j3: '总库存 - J3',
};

export const TYPE_FILTER_OPTIONS = {
  j1: ['Service Line', 'Sake', 'Kitchen', 'Sushi Bar'],
  j2: ['Service Line', 'Kitchen', 'Sushi Bar'],
  j3: ['Service Line', 'Sake', 'Kitchen', 'Sushi Bar'],
};

export const CENTRAL_SUPPLY_SYSTEMS = ['j1', 'j2', 'j3'];

export const VIEW_REDIRECT_MAP = {
  records: 'stockeditall-v2',
  list: 'stocklistall-v2',
  remark: 'stockremark',
  product: 'stockproductname',
  sot: 'stocksot',
};

export const STOCK_COLUMN_LABEL = {
  central: '库存数量',
  j1: '库存总量',
  j2: '库存总量',
  j3: '库存总量',
};

export const SEARCH_PLACEHOLDER = {
  central: '输入关键字搜索...',
  j1: '搜索序号、货品编号、货品、库存数量、规格、单价、总价...',
  j2: '搜索货品名称、编号或规格单位...',
  j3: '搜索序号、货品编号、货品、库存数量、规格、单价、总价...',
};
